<?php
//declare(strict_types = 1);
namespace Morpho\Core;

use Morpho\Base\Node as BaseNode;
use Morpho\Base\IEventManager;
use Morpho\Code\ClassTypeDiscoverer;
use Morpho\Db\Sql\Db;

abstract class ModuleManager extends Node implements IEventManager {
    const ENABLED     = 0b001;  // (installed enabled)
    const DISABLED    = 0b010;  // (installed disabled)
    const INSTALLED   = 0b011;  // (installed enabled | installed disabled)
    const UNINSTALLED = 0b100;  // (uninstalled (not installed))
    const ALL         = 0b111;  // (all above (installed | uninstalled))

    protected $fallbackMode = false;

    protected $fallbackModules = [
        'System',
        'User',
        'Bootstrap',
    ];

    protected $eventHandlers;

    protected $name = 'ModuleManager';

    protected $db;

    protected $tableName = 'module';
    
    protected $moduleListProvider;
    
    protected $autoloader;
    
    public function __construct(Db $db = null, \Traversable $moduleListProvider = null, ModuleAutoloader $autoloader = null) {
        $this->db = $db;
        $this->moduleListProvider = $moduleListProvider;
        $this->autoloader = $autoloader;
    }

    public function isFallbackMode(bool $flag = null): bool {
        if (null !== $flag) {
            $this->fallbackMode = $flag;
        }
        return $this->fallbackMode;
    }

    public function dispatch($request)/*: void */ {
        do {
            try {
                $request->isDispatched(true);

                list($moduleName, $controllerName, $actionName) = $request->getHandler();

                if (empty($moduleName) || empty($controllerName) || empty($actionName)) {
                    $this->actionNotFound($moduleName, $controllerName, $actionName);
                }

                $this->trigger('beforeDispatch', ['request' => $request]);

                $module = $this->getChild($moduleName);
                $controller = $module->getChild($controllerName);
                $controller->dispatch($request);

                $this->trigger('afterDispatch', ['request' => $request]);
            } catch (\Throwable $e) {
                $this->trigger('dispatchError', ['request' => $request, 'exception' => $e]);
            }
        } while (false === $request->isDispatched());
    }

    public function on(string $eventName, callable $handler)/*: void */ {
        $this->initEventHandlers();
        $this->eventHandlers[$eventName][] = $handler;
    }

    /**
     * @return mixed
     */
    public function trigger(string $eventName, array $args = null) {
        $event = [$eventName, $args];
        $this->initEventHandlers();
        if (isset($this->eventHandlers[$eventName])) {
            foreach ($this->eventHandlers[$eventName] as $handler) {
                if (false === is_callable($handler)) {
                    $handler = [
                        $this->getChild($handler['moduleName']),
                        $handler['method'],
                    ];
                }
                if (!is_callable($handler)) {
                    continue;
                }
                $result = call_user_func($handler, $event);
                if (null !== $result) {
                    return $result;
                }
            }
        }
    }

    public function installModule(string $moduleName)/*: void */ {
        $db = $this->db;
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->getChild($moduleName);

                $db->schemaManager()->createTables($module->getTableDefinitions());

                $module->install($db);

                $db->insertRow($this->tableName, ['name' => $moduleName, 'status' => self::DISABLED]);
            }
        );
        $this->rebuildEvents($moduleName);
        $this->clearCache();
    }

    public function uninstallModule(string $moduleName)/*: void */ {
        $db = $this->db;
        $moduleId = $db->selectCell("id FROM {$this->tableName} WHERE name = ? AND status = ?", [$moduleName, self::DISABLED]);
        if (!$moduleId) {
            throw new \LogicException("Can't uninstall the module '$moduleName', only disabled modules can be uninstalled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName, $moduleId) {
                $this->getChild($moduleName)
                    ->uninstall($db);
                $db->deleteRows('event', ['moduleId' => $moduleId]);
                $db->deleteRows($this->tableName, ['id' => $moduleId]);
            }
        );
        $this->rebuildEvents($moduleName);
        $this->clearCache();
    }

    public function enableModule(string $moduleName)/*: void */ {
        $db = $this->db;
        if ($db->selectBool("id FROM $this->tableName WHERE name = ? AND status = ?", [$moduleName, self::ENABLED])) {
            throw new \LogicException("The module '$moduleName' is already enabled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $this->getChild($moduleName)
                    ->enable($db);
                $db->updateRows($this->tableName, ['status' => self::ENABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function disableModule(string $moduleName)/*: void */ {
        $db = $this->db;
        $exists = (bool)$db->selectCell("id FROM $this->tableName WHERE name = ? AND status = ?", [$moduleName, self::ENABLED]);
        if (!$exists) {
            throw new \LogicException("Can't disable the module '$moduleName', only enabled modules can be disabled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $this->getChild($moduleName)
                    ->disable($db);
                $db->updateRows($this->tableName, ['status' => self::DISABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function installAndEnableModule(string $moduleName)/*: void */ {
        $db = $this->db;
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->getChild($moduleName);

                $db->schemaManager()->createTables($module->getTableDefinitions());

                $module->install($db);
                $db->insertRow($this->tableName, ['name' => $moduleName, 'status' => self::DISABLED]);

                $module->enable($db);
                $db->updateRows($this->tableName, ['status' => self::ENABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
        $this->clearCache();
    }

    public function rebuildEvents($moduleName = null)/*: void */ {
        $modules = null !== $moduleName ? [$moduleName] : $this->listEnabledModules();
        $db = $this->db;
        foreach ($modules as $moduleName) {
            $db->transaction(function () use ($moduleName) {
                $moduleRow = $this->db->selectRow('id, status FROM module WHERE name = ?', [$moduleName]);
                if ($moduleRow) {
                    $this->db->runQuery("DELETE FROM event WHERE moduleId = ?", [$moduleRow['id']]);
                    foreach ($this->getEventsMeta($this->getChild($moduleName)) as $eventMeta) {
                        $this->db->insertRow('event', array_merge($eventMeta, ['moduleId' => $moduleRow['id']]));
                    }
                }
            });
        }
    }

    public function isEnabledModule(string $moduleName): bool {
        return in_array($moduleName, $this->listEnabledModules(), true);
    }

    public function isDisabledModule(string $moduleName): bool {
        return in_array($moduleName, $this->listDisabledModules(), true);
    }

    public function isUninstalledModule(string $moduleName): bool {
        return in_array($moduleName, $this->listUninstalledModules(), true);
    }

    public function isInstalledModule(string $moduleName): bool {
        return in_array($moduleName, $this->listInstalledModules(), true);
    }

    public function listModules($state): array {
        $modules = [];
        if ($state & self::ENABLED) {
            $modules = array_merge($modules, array_values($this->listEnabledModules()));
        }
        if ($state & self::DISABLED) {
            $modules = array_merge($modules, $this->listDisabledModules());
        }
        if ($state & self::UNINSTALLED) {
            $modules = array_merge($modules, $this->listUninstalledModules());
        }
        return $modules;
    }

    public function listAllModules(): array {
        if ($this->fallbackMode) {
            return [];
        }
        return iterator_to_array($this->moduleListProvider, false);
    }

    public function listInstalledModules(): array {
        return $this->fallbackMode
            ? []
            : $this->db->selectColumn("name FROM $this->tableName ORDER BY name, weight");
    }

    public function listUninstalledModules(): array {
        if ($this->fallbackMode) {
            return $this->fallbackModules;
        }
        return array_diff($this->listAllModules(), $this->listInstalledModules());
    }

    public function listEnabledModules(): array {
        return $this->fallbackMode
            ? []
            : $this->db->selectMap("id, name FROM $this->tableName WHERE status = ? ORDER BY name, weight", [self::ENABLED]);
    }

    public function listDisabledModules(): array {
        return $this->fallbackMode
            ? []
            : $this->db->selectMap("id, name FROM $this->tableName WHERE status = ? ORDER BY name, weight", [self::DISABLED]);
    }

    public function setDb(Db $db) {
        $this->db = $db;
    }

    protected function clearCache()/*: void */ {
        $this->autoloader->clearCache();
    }

    protected function loadChild(string $name): BaseNode {
        $this->autoloader->registerModule($name);
        return parent::loadChild($name);
    }
    
    protected function childNameToClass(string $moduleName) {
        $class = $moduleName . '\\' . MODULE_SUFFIX;
        return class_exists($class) ? $class : __NAMESPACE__ . '\\Module';
    }

    protected function initEventHandlers()/*: void */ {
        if (null !== $this->eventHandlers) {
            return;
        }
        if ($this->fallbackMode) {
            $this->eventHandlers = $this->getFallbackModeEventHandlers();
        } else {
            $sql = "e.name as eventName, e.method, m.name AS moduleName
            FROM event e
            INNER JOIN $this->tableName m
                ON e.moduleId = m.id
            WHERE m.status = ?
            ORDER BY e.priority DESC, m.weight ASC, m.name ASC";
            $lines = $this->db->selectRows($sql, [self::ENABLED]);
            if (!count($lines)) {
                // For some reason the events can be lost in the database, so we need fallback.
                $this->eventHandlers = $this->getFallbackModeEventHandlers();
                return;
            }
            $this->eventHandlers = [];
            foreach ((array)$lines as $line) {
                $this->eventHandlers[$line['eventName']][] = $line;
            }
        }
    }

    protected function getEventsMeta($module): array {
        $rClass = new \ReflectionClass($module);
        $rClasses = [$rClass];
        while ($rClass = $rClass->getParentClass()) {
            $rClasses[] = $rClass;
        }
        $rClasses = array_reverse($rClasses);
        $regexp = '~@Listen\s+(?<eventName>[a-zA-Z_][a-zA-Z_0-9]*)(\s+(?<priority>(?:\d*\.\d+)|(?:\d+\.\d*)|(\d+)))?~s';
        $foundEvents = [];
        foreach ($rClasses as $rClass) {
            $filter = \ReflectionMethod::IS_PUBLIC ^ (\ReflectionMethod::IS_ABSTRACT | \ReflectionMethod::IS_STATIC);
            foreach ($rClass->getMethods($filter) as $rMethod) {
                $methodName = $rMethod->getName();
                if ($methodName === '__construct') {
                    continue;
                }
                $docComment = $rMethod->getDocComment();
                if (false !== $docComment) {
                    if (preg_match_all($regexp, $docComment, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            $eventName = $match['eventName'];
                            $priority = isset($match['priority']) ? $match['priority'] : 0;
                            $foundEvents[$methodName][$eventName] = $priority;
                        }
                        continue;
                    }
                }
                if ($rMethod->class === $rClass->name) {
                    // If the child class defines a method with the same name, don't inherit
                    // doc-comments.
                    unset($foundEvents[$methodName]);
                }
            }
        }
        $events = [];
        foreach ($foundEvents as $methodName => $events1) {
            foreach ($events1 as $eventName => $priority) {
                $events[] = [
                    'name'     => $eventName,
                    'priority' => $priority,
                    'method'   => $methodName,
                ];
            }
        }
        return $events;
    }

    protected function getFallbackModeEventHandlers(): array {
        return [];
    }

    abstract protected function actionNotFound($moduleName, $controllerName, $actionName)/*: void */;
}
