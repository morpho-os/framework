<?php
declare(strict_types=1);

namespace Morpho\Core;

use function Morpho\Base\classify;
use Morpho\Base\IEventManager;
use Morpho\Db\Db;

abstract class ModuleManager extends Node implements IEventManager {
    const ENABLED     = 0x1;  // 001 (installed enabled)
    const DISABLED    = 0x2;  // 010 (installed disabled)
    const UNINSTALLED = 0x4;  // 100 (uninstalled (not installed))
    const ALL         = 0x7;  // 111 (all above (installed | uninstalled))

    protected $fallbackMode = false;

    protected $eventHandlers;

    protected $name = 'ModuleManager';

    protected $db;

    protected $tableName = 'module';

    public function __construct(Db $db) {
        $this->db = $db;
    }

    public function setDb(Db $db) {
        $this->db = $db;
    }

    /**
     * @param bool|null $flag
     * @return bool
     */
    public function isFallbackMode($flag = null) {
        if (null !== $flag) {
            $this->fallbackMode = $flag;
        }
        return $this->fallbackMode;
    }

    public function dispatch($request) {
        do {
            try {
                $request->isDispatched(true);

                list($moduleName, $controllerName, $actionName) = [
                    $request->getModuleName(),
                    $request->getControllerName(),
                    $request->getActionName()
                ];

                if (empty($moduleName) || empty($controllerName) || empty($actionName)) {
                    $this->actionNotFound($moduleName, $controllerName, $actionName);
                }

                $this->trigger('beforeDispatch', ['request' => $request]);

                $controller = $this->getChild($moduleName)
                    ->getChild($controllerName);

                $controller->dispatch($request);

                $this->trigger('afterDispatch', ['request' => $request]);
            } catch (\Exception $e) {
                $this->trigger('dispatchError', ['request' => $request, 'exception' => $e]);
            }
        } while (false === $request->isDispatched());
    }

    public function on(string $eventName, callable $handler) {
        $this->initEventHandlers();
        $this->eventHandlers[$eventName][] = $handler;
    }

    public function trigger(string $eventName, array $args = null) {
        $event = [$eventName, $args];
        $this->initEventHandlers();
        if (isset($this->eventHandlers[$eventName])) {
            foreach ($this->eventHandlers[$eventName] as $handler) {
                if (false === is_callable($handler)) {
                    $handler = [
                        $this->getChild($handler['moduleName']),
                        $handler['method']
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

    public function installModule(string $moduleName) {
        $db = $this->db;
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->getChild($moduleName);

                $db->createTables($module->getTableDefinitions());

                $module->install($db);

                $db->insertRow($this->tableName, ['name' => $moduleName, 'status' => self::DISABLED]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function uninstallModule(string $moduleName) {
        $db = $this->db;
        $exists = $db->selectBool("id FROM {$this->tableName} WHERE name = ? AND status = ?", [$moduleName, self::DISABLED]);
        if (!$exists) {
            throw new \LogicException("Can't uninstall the module '$moduleName', only disabled modules can be uninstalled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $this->getChild($moduleName)
                    ->uninstall($db);
                $db->deleteRows($this->tableName, ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function enableModule(string $moduleName) {
        $db = $this->db;
        if ($db->selectBool("id FROM $this->tableName WHERE name = ? AND status = ?", [$moduleName, self::ENABLED])) {
            throw new \LogicException("The module '$moduleName' is already enabled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $this->getChild($moduleName)
                    ->enable($db);
                $db->updateRow($this->tableName, ['status' => self::ENABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function disableModule(string $moduleName) {
        $db = $this->db;
        $exists = (bool)$db->selectCell("id FROM $this->tableName WHERE name = ? AND status = ?", [$moduleName, self::ENABLED]);
        if (!$exists) {
            throw new \LogicException("Can't disable the module '$moduleName', only enabled modules can be disabled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $this->getChild($moduleName)
                    ->disable($db);
                $db->updateRow($this->tableName, ['status' => self::DISABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function rebuildEvents($moduleName = null) {
        $modules = null !== $moduleName ? [$moduleName] : $this->listEnabledModules();
        foreach ($modules as $moduleName) {
            $moduleId = $this->getModuleIdByName($moduleName);
            $this->db->query("DELETE FROM event WHERE moduleId = ?", [$moduleId]);
            foreach ($this->getEvents($this->getChild($moduleName), $moduleId) as $event) {
                $this->db->insertRow('event', array_merge($event, ['moduleId' => $moduleId]));
            }
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
        return $this->fallbackMode
            ? []
            : array_merge($this->listUninstalledModules(), $this->listInstalledModules());
    }

    public function listInstalledModules(): array {
        return $this->fallbackMode
            ? []
            : array_merge($this->listEnabledModules(), $this->listDisabledModules());
    }

    public function listUninstalledModules() {
        // @TODO: Resolve dependencies automatically.
        if ($this->fallbackMode) {
            $exclude = [];
            $modules = [
                'System',
                'User',
                'Bootstrap',
            ];
        } else {
            $exclude = $this->db->selectColumn("name FROM $this->tableName ORDER BY name");
            $modules = [];
        }
        $moduleAutoloader = $this->getModuleAutoloader();
        foreach ($moduleAutoloader as $class => $path) {
            $isModuleClass = substr($class, -strlen(MODULE_SUFFIX)) === MODULE_SUFFIX;
            if (!$isModuleClass) {
                continue;
            }
            $moduleName = $this->classToName($class);
            if (in_array($moduleName, $exclude)) {
                continue;
            }
            if (!in_array($moduleName, $modules)) {
                $modules[] = $moduleName;
            }
        }

        return $modules;
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

    protected function getModuleIdByName(string $moduleName) {
        return $this->db->selectCell("id FROM module WHERE name = ?", [$moduleName]);
    }

    protected function childNameToClass(string $moduleName): string {
        return $moduleName . '\\' . MODULE_SUFFIX;
    }
    protected function classToName(string $class): string {
        $suffixLength = strlen(MODULE_SUFFIX);
        if (substr($class, -$suffixLength) !== MODULE_SUFFIX) {
            throw new \UnexpectedValueException("The module class '$class' must end with the '" . MODULE_SUFFIX . "' suffix");
        }
        // Module name := <Name> NS_SEP "Module".
        return substr($class, 0, -($suffixLength + 1));
    }

    protected function getModuleAutoloader() {
        return $this->serviceManager->get('moduleAutoloader');
    }

    protected function initEventHandlers() {
        if (null !== $this->eventHandlers) {
            return;
        }
        if ($this->fallbackMode) {
            $this->eventHandlers = $this->getFallbackModeEventHandlers();
        } else {
            $this->eventHandlers = [];
            $sql = "e.name as eventName, e.method, m.name AS moduleName
            FROM event e
            INNER JOIN $this->tableName m
                ON e.moduleId = m.id
            WHERE m.status = ?
            ORDER BY e.priority DESC, m.weight ASC, m.name ASC";
            $lines = $this->db->selectRows($sql, [self::ENABLED]);
            foreach ((array)$lines as $line) {
                $this->eventHandlers[$line['eventName']][] = $line;
            }
        }
    }

    protected function getEvents(Module $module): array {
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
            foreach ($rClass->getMethods($filter) as $method) {
                $docComment = $method->getDocComment();
                if (false === $docComment) {
                    continue;
                }
                if (preg_match_all($regexp, $docComment, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $eventName = $match['eventName'];
                        $priority = isset($match['priority']) ? $match['priority'] : 0;
                        $foundEvents[$eventName][$method->getName()] = $priority;
                    }
                }
            }
        }
        $events = [];
        foreach ($foundEvents as $eventName => $methods) {
            foreach ($methods as $method => $priority) {
                $events[] = [
                    'name' => $eventName,
                    'priority' => $priority,
                    'method' => $method,
                ];
            }
        }

        return $events;
    }

    protected function getFallbackModeEventHandlers(): array {
        return [];
    }

    abstract protected function actionNotFound($moduleName, $controllerName, $actionName);
}
