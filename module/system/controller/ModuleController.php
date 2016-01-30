<?php
namespace System\Controller;

use Morpho\Code\CodeGenerator;
use Morpho\Web\Controller;
use Morpho\Web\ModuleManager;

class ModuleController extends Controller {
    /**
     * @Title List modules
     */
    public function listAction() {
    }

    public function newAction() {
        dd();
    }

    /**
     * @POST
     */
    public function installAction() {
        return $this->processModule(function ($moduleManager, $moduleName) {
            $moduleManager->installAndEnableModule($moduleName);
        });
    }

    /**
     * @POST
     */
    public function uninstallAction() {
        return $this->processModule(function ($moduleManager, $moduleName) {
            $moduleManager->uninstallModule($moduleName);
        });
    }

    /**
     * @POST
     */
    public function disableAction() {
        return $this->processModule(function ($moduleManager, $moduleName) {
            $moduleManager->disableModule($moduleName);
        });
    }

    /**
     * @POST
     */
    public function enableAction() {
        return $this->processModule(function ($moduleManager, $moduleName) {
            $moduleManager->enableModule($moduleName);
        });
    }

    /**
     * @POST
     */
    public function configureAction() {
        dd();
    }

    /**
     * @Title Rebuild routes
     */
    public function rebuildRoutesAction() {
        $this->serviceManager->get('router')->rebuildRoutes();
        $this->addSuccessMessage("Routes were rebuilt successfully.");
        $this->redirectToUri('/');
    }

    /**
     * @Title Rebuild events
     */
    public function rebuildEventsAction() {
        $this->serviceManager->get('moduleManager')->rebuildEvents();
        $this->addSuccessMessage("Events were rebuilt successfully.");
        $this->redirectToUri('/');
    }

    public function isInstalledModule(array $module) {
        //return (int) $module['status'] & (ModuleManager::DISABLED | ModuleManager::ENABLED);
        return !empty($module['id']);
    }

    public function isEnabledModule(array $module) {
        return (int) $module['status'] & ModuleManager::ENABLED;
    }

    public function listModules(): array {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $modules = $this->getDb()->selectRows('* FROM module ORDER BY weight, name');
        foreach ($moduleManager->listUninstalledModules() as $moduleName) {
            $module = $moduleManager->getChild($moduleName);
            $modules[] = [
                'id' => null,
                'name' => $module->getName(),
                'status' => ModuleManager::UNINSTALLED,
            ];
        }
        return $modules;
    }

    private function processModule(\Closure $process) {
        $moduleName = key(array_values($_POST)[0]);
        $process($this->serviceManager->get('moduleManager'), $moduleName);
        return $this->redirectToUri('/system/module/list');
    }
}
