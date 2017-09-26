<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types = 1);
namespace Morpho\Core;

use Morpho\Base\ClassNotFoundException;
use Morpho\Base\IEventManager;
use Morpho\Db\Sql\Db;
use Morpho\Base\Node as BaseNode;
use Morpho\Web\ModuleFs;

abstract class ModuleManager extends Node implements IEventManager {
    public const ENABLED     = 0b001;  // (installed enabled)
    public const DISABLED    = 0b010;  // (installed disabled)
    public const INSTALLED   = 0b011;  // (installed enabled | installed disabled)
    public const UNINSTALLED = 0b100;  // (uninstalled (not installed))
    public const ALL         = 0b111;  // (all above (installed | uninstalled))

    public const SYSTEM_MODULE = VENDOR . '/system';
//    public const USER_MODULE   = VENDOR . '/user';

    protected $eventHandlers;

    protected $name = 'ModuleManager';
    protected $type = 'ModuleManager';

    protected $tableName = 'module';

    protected $db;

    protected $fs;

    protected $maxNoOfDispatchIterations = 30;

    public function __construct(Db $db = null, Fs $fs) {
        $this->db = $db;
        $this->fs = $fs;
    }

    public function fs(): Fs {
        return $this->fs;
    }

    public function dispatch($request): void {
        $i = 0;
        do {
            if ($i >= $this->maxNoOfDispatchIterations) {
                throw new \RuntimeException("Dispatch loop has occurred {$this->maxNoOfDispatchIterations} times");
            }
            try {
                $request->isDispatched(true);

                $this->trigger('beforeDispatch', ['request' => $request]);

                $controller = $this->controller(...$request->handler());
                $controller->dispatch($request);

                $this->trigger('afterDispatch', ['request' => $request]);
            } catch (\Throwable $e) {
                $this->trigger('dispatchError', ['request' => $request, 'exception' => $e]);
            }
            $i++;
        } while (false === $request->isDispatched());
    }

    public function setMaxNoOfDispatchIterations(int $n) {
        $this->maxNoOfDispatchIterations = $n;
        return $this;
    }

    public function maxNoOfDispatchIterations(): int {
        return $this->maxNoOfDispatchIterations;
    }

    public function controller($moduleName, $controllerName, $actionName): Controller {
        if (empty($moduleName) || empty($controllerName) || empty($actionName)) {
            $this->actionNotFound($moduleName, $controllerName, $actionName);
        }
        $module = $this->offsetGet($moduleName);
        return $module->offsetGet($controllerName);
    }

    public function on(string $eventName, callable $handler): void {
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
                        $this->offsetGet($handler['moduleName']),
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

    public function installModule(string $moduleName): void {
        $db = $this->db;
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->offsetGet($moduleName);

                $db->schemaManager()->createTables($module->tableDefinitions());

                $module->install($db);

                $db->insertRow($this->tableName, ['name' => $moduleName, 'status' => self::DISABLED]);
            }
        );
        $this->rebuildEvents($moduleName);
        $this->clearCache();
    }

    public function uninstallModule(string $moduleName): void {
        $db = $this->db;
        $moduleId = $db->select("id FROM {$this->tableName} WHERE name = ? AND status = ?", [$moduleName, self::DISABLED])->cell();
        if (!$moduleId) {
            throw new \LogicException("Can't uninstall the module '$moduleName', only disabled modules can be uninstalled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName, $moduleId) {
                $module = $this->offsetGet($moduleName);
                $module->uninstall($db);
                $db->deleteRows('event', ['moduleId' => $moduleId]);
                $db->deleteRows($this->tableName, ['id' => $moduleId]);
            }
        );
        $this->rebuildEvents($moduleName);
        $this->clearCache();
    }

    public function enableModule(string $moduleName): void {
        $db = $this->db;
        if ($db->select("id FROM $this->tableName WHERE name = ? AND status = ?", [$moduleName, self::ENABLED])->bool()) {
            throw new \LogicException("The module '$moduleName' is already enabled");
        }
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->offsetGet($moduleName);
                $module->enable($db);
                $db->updateRows($this->tableName, ['status' => self::ENABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function disableModule(string $moduleName): void {
        /*
        $db = $this->db;
        $exists = (bool)$db->select("id FROM $this->tableName WHERE name = ? AND status = ?", [$moduleName, self::ENABLED])->cell();
        if (!$exists) {
            throw new \LogicException("Can't disable the module '$moduleName', only enabled modules can be disabled");
        }
        */
        $this->db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->offsetGet($moduleName);
                $module->disable($db);
                $db->updateRows($this->tableName, ['status' => self::DISABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
    }

    public function installAndEnableModule(string $moduleName): void {
        $db = $this->db;
        $db->transaction(
            function (Db $db) use ($moduleName) {
                $module = $this->offsetGet($moduleName);

                $db->schemaManager()->createTables($module->tableDefinitions());

                $module->install($db);
                $db->insertRow($this->tableName, ['name' => $moduleName, 'status' => self::DISABLED]);

                $module->enable($db);
                $db->updateRows($this->tableName, ['status' => self::ENABLED], ['name' => $moduleName]);
            }
        );
        $this->rebuildEvents($moduleName);
        $this->clearCache();
    }

    public function rebuildEvents($moduleName = null): void {
        $modules = null !== $moduleName ? [$moduleName] : $this->enabledModuleNames();
        $db = $this->db;
        foreach ($modules as $moduleName) {
            $db->transaction(function () use ($moduleName) {
                $moduleRow = $this->db->select('id, status FROM module WHERE name = ?', [$moduleName])->row();
                if ($moduleRow) {
                    $this->db->eval("DELETE FROM event WHERE moduleId = ?", [$moduleRow['id']]);
                    $module = $this->offsetGet($moduleName);
                    foreach ($this->eventsMeta($module) as $eventMeta) {
                        $this->db->insertRow('event', array_merge($eventMeta, ['moduleId' => $moduleRow['id']]));
                    }
                }
            });
        }
    }

    public function isEnabledModule(string $moduleName): bool {
        return in_array($moduleName, $this->enabledModuleNames(), true);
    }

    public function isDisabledModule(string $moduleName): bool {
        return in_array($moduleName, $this->disabledModuleNames(), true);
    }

    public function isUninstalledModule(string $moduleName): bool {
        return in_array($moduleName, $this->uninstalledModuleNames(), true);
    }

    public function isInstalledModule(string $moduleName): bool {
        return in_array($moduleName, $this->installedModuleNames(), true);
    }

    public function moduleNames(int $state = null): array {
        if (null === $state) {
            $state = self::ALL;
        }
        $modules = [];
        if ($state & self::ENABLED) {
            $modules = array_merge($modules, array_values($this->enabledModuleNames()));
        }
        if ($state & self::DISABLED) {
            $modules = array_merge($modules, $this->disabledModuleNames());
        }
        if ($state & self::UNINSTALLED) {
            $modules = array_merge($modules, $this->uninstalledModuleNames());
        }
        return $modules;
    }

    public function allModuleNames(): array {
        $moduleNames = $this->fs->moduleNames();
        return is_array($moduleNames) ? $moduleNames : iterator_to_array($moduleNames, false);
    }

    public function installedModuleNames(): array {
        return $this->db->select("name FROM $this->tableName ORDER BY name, weight")->column();
    }

    public function uninstalledModuleNames(): array {
        return array_diff($this->allModuleNames(), $this->installedModuleNames());
    }

    public function enabledModuleNames(): array {
        return $this->db->select("id, name FROM $this->tableName WHERE status = ? ORDER BY name, weight", [self::ENABLED])->map();
    }

    public function disabledModuleNames(): array {
        return $this->db->select("id, name FROM $this->tableName WHERE status = ? ORDER BY name, weight", [self::DISABLED])->map();
    }

    public function setDb(Db $db) {
        $this->db = $db;
    }

    protected function clearCache(): void {
        $this->fs->clearCache();
    }

    protected function childNameToClass(string $moduleName) {
        $class = $this->fs->moduleClass($moduleName);
        return $class ?: false;
    }

    protected function loadChild(string $moduleName): BaseNode {
        $fs = $this->fs;
        $class = $this->childNameToClass($moduleName);
        if (false === $class) {
            throw new ClassNotFoundException("Unable to load the module '$moduleName'");
        }
        $fs->registerModuleAutoloader($moduleName);
        return new $class($moduleName, new ModuleFs($fs->moduleDirPath($moduleName)));
    }

    protected function initEventHandlers(): void {
        if (null !== $this->eventHandlers) {
            return;
        }
        $sql = "e.name as eventName, e.method, m.name AS moduleName
        FROM event e
        INNER JOIN $this->tableName m
            ON e.moduleId = m.id
        WHERE m.status = ?
        ORDER BY e.priority DESC, m.weight ASC, m.name ASC";
        $lines = $this->db->select($sql, [self::ENABLED])->rows();
        /*if (!count($lines)) {
            @TODO
            // For some reason the events can be lost in the database, so we need fallback.
            $this->eventHandlers = $this->fallbackModeEventHandlers();
            return;
        }*/
        $this->eventHandlers = [];
        foreach ((array)$lines as $line) {
            $this->eventHandlers[$line['eventName']][] = $line;
        }
    }

    abstract protected function actionNotFound($moduleName, $controllerName, $actionName): void;

    protected function eventsMeta($module): iterable {
        return (new EventsMetaProvider())($module);
    }
}
