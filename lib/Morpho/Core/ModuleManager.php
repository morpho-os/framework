<?php
declare(strict_types=1);

namespace Morpho\Core;

use Morpho\Base\{NotImplementedException, Node as BaseNode};
use Morpho\Db\Db;

abstract class ModuleManager extends Node {
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

    public function on(string $event, callable $handler) {
        $this->initEventHandlers();
        $this->eventHandlers[$event][] = $handler;
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
                $result = call_user_func($handler, $event);
                if (null !== $result) {
                    return $result;
                }
            }
        }
    }

    public function install(string $moduleName) {
        $db = $this->db;
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->getChild($moduleName);
                $db->insertRow($this->tableName, ['name' => $module->getName(), 'status' => self::DISABLED]);
                $module->install($db);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function uninstall(string $moduleName) {
        throw new NotImplementedException();
        /*
        $db = $this->db;
        $exists = $db->selectBool('id FROM module WHERE name = ? AND status = 0', [$moduleName]);
        if (!$exists) {
            throw new \LogicException("Can't uninstall the module '$moduleName', only disabled modules can be uninstalled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                d($moduleName);
                /*
                $module = $this->get($moduleName);
                $db->deleteEntity($module);

                $module->uninstall($db);
            }
        );
        $this->rebuildEvents($moduleName);
        */
    }

    public function enable(string $moduleName) {
        $db = $this->db;
        if ($db->selectBool('id FROM module WHERE name = ? AND status = ?', [$moduleName, self::ENABLED])) {
            throw new \LogicException("Can't enable the already enabled module '$moduleName'");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $db->updateRow('module', ['status' => self::ENABLED], ['name' => $moduleName]);
                $module = $this->getChild($moduleName);
                $module->enable($db);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function disable(string $moduleName) {
        $db = $this->db;
        $exists = (bool)$db->selectCell('id FROM module WHERE name = ? AND status = ?', [$moduleName, self::DISABLED]);
        if (!$exists) {
            throw new \LogicException("Can't disable the module '$moduleName', only enabled modules can be disabled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                throw new NotImplementedException();
                /*
                $module = $this->get($moduleName);
                $module->isEnabled(false);
                $db->saveEntity($module);

                $module->disable($db);
                */
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function listModules($state): array {
        $modules = [];
        if ($state & self::ENABLED) {
            $modules = array_merge($modules, array_values($this->listEnabled()));
        }
        if ($state & self::DISABLED) {
            $modules = array_merge($modules, $this->listDisabled());
        }
        if ($state & self::UNINSTALLED) {
            $modules = array_merge($modules, $this->listUninstalled());
        }

        return $modules;
    }

    public function rebuildEvents($moduleName = null) {
        $modules = null !== $moduleName ? [$moduleName] : $this->listEnabled();
        foreach ($modules as $moduleName) {
            $moduleId = $this->getModuleIdByName($moduleName);
            $this->db->query("DELETE FROM event WHERE moduleId = ?", [$moduleId]);
            foreach ($this->getEventsOfModule($this->getChild($moduleName), $moduleId) as $event) {
                $this->db->insertRow('event', array_merge($event, ['moduleId' => $moduleId]));
            }
        }
    }

    public function isModuleEnabled(string $moduleName): bool {
        return in_array($moduleName, $this->listEnabled());
    }

    public function isModuleDisabled(string $moduleName): bool {
        return in_array($moduleName, $this->listDisabled());
    }

    public function isModuleUninstalled(string $moduleName): bool {
        return in_array($moduleName, $this->listUninstalled());
    }

    public function listAll(): array {
        return $this->fallbackMode ? [] : array_merge($this->listUninstalled(), $this->listInstalled());
    }

    public function listInstalled(): array {
        return $this->fallbackMode ? [] : array_merge($this->listEnabled(), $this->listDisabled());
    }

    public function listUninstalled() {
        // @TODO: Resolve dependencies automatically.
        if ($this->fallbackMode) {
            $exclude = [];
            $modules = [
                'System',
                'User',
                'Bootstrap',
            ];
        } else {
            $exclude = $this->db->selectColumn('name FROM module ORDER BY name');
            $modules = [];
        }
        $moduleAutoloader = $this->getModuleAutoloader();
        foreach ($moduleAutoloader as $class => $path) {
            if (substr($class, -(strlen(MODULE_SUFFIX) + 1)) !== '\\' . MODULE_SUFFIX) {
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

    public function listEnabled(): array {
        return $this->fallbackMode
            ? []
            : $this->db->selectMap('id, name FROM module WHERE status = 1 ORDER BY name, weight');
    }

    public function listDisabled(): array {
        return $this->fallbackMode
            ? []
            : $this->db->selectMap('id, name FROM module WHERE status = 0 ORDER BY name, weight');
    }

    protected function getModuleIdByName(string $moduleName) {
        return $this->db->selectCell("id FROM module WHERE name = ?", [$moduleName]);
    }

    protected function nameToClass(string $moduleName): string {
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
            INNER JOIN module m
                ON e.moduleId = m.id
            WHERE m.status = '1'
            ORDER BY e.priority DESC, m.weight ASC, m.name ASC";
            $lines = $this->db->selectRows($sql);
            foreach ((array)$lines as $line) {
                $this->eventHandlers[$line['eventName']][] = $line;
            }
        }
    }

    protected function getEventsOfModule(Module $module): array {
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
