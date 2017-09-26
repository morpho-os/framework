<?php
namespace Morpho\System\Controller;

use function Morpho\Base\{classify};
use Morpho\Fs\{File, Directory, Path};
use Morpho\Web\Controller;
use Morpho\Web\ModuleManager;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;

class ModuleController extends Controller {
    public function indexAction() {
        $this->setView('list');
        return $this->listAction();
    }

    /**
     * @Title List modules
     */
    public function listAction() {
    }

    public function newAction() {
        $formData = $this->session('createAction');
        return [
            'formData' => $formData->toArray(),
        ];
    }

    public function createAction() {
        $postData = $this->post();
        $this->session(__FUNCTION__)->formData = $postData;

        $moduleName = classify($postData['module']['name']);
        $this->writeModuleClass($moduleName, !empty($postData['module']['isTheme']));

        $fixPath = function ($path) {
            return str_replace('___', '/', $path);
        };
        $pathsToCreate = function (string $key) use ($fixPath, $postData): array {
            $postData = array_keys((array) $postData[$key]);
            return array_map($fixPath, $postData);
        };
        $moduleDirPath = $this->moduleDirPath($moduleName);
        foreach ($pathsToCreate('file') as $fileRelPath) {
            File::createEmpty(Path::combine($moduleDirPath, $fileRelPath));
        }
        foreach ($pathsToCreate('dir') as $dirRelPath) {
            Directory::create(Path::combine($moduleDirPath, $dirRelPath));
        }

        if (!empty($postData['module']['enable'])) {
            $moduleManager = $this->parent('ModuleManager');
            $moduleManager->installAndEnableModule($moduleName);
        }

        $this->redirectToUri('/system/module/list');
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
     * @POST
     */
    public function deleteFromDiskAction() {
        $moduleName = key(array_values($_POST)[0]);
        if (!$this->serviceManager->get('moduleManager')->isUninstalledModule($moduleName)) {
            $this->addErrorMessage("To delete the module '{moduleName}' from the disk it must be uninstalled first.", ['moduleName' => classify($moduleName)]);
            $this->redirectToUri('/system/module/list');
            return;
        }
        $moduleDirPath = $this->moduleDirPath($moduleName);
        if (!is_dir($moduleDirPath)) {
            $this->addErrorMessage("The module directory for the module '{moduleName}' does not exist.", ['moduleName' => classify($moduleName)]);
            $this->redirectToUri('/system/module/list');
            return;
        } else {
            Directory::delete($moduleDirPath);
        }
        $this->addSuccessMessage("The module '{moduleName}' was successfully deleted from the disk.", ['moduleName' => classify($moduleName)]);
        $this->redirectToUri('/system/module/list');
        return;
    }

    // --------------------------------------------------------------------------------

    public function moduleDirHierarchy(): array {
        return [
            ['controller'],
            ['doc'],
            ['domain'],
            ['lib'],
            [
                'test', [
                    'c',
                    ['d'],
                    'f'
                ],
            ],
            ['update'],
            ['view'],
            'composer.json',
            //'Module.php',
        ];
    }

    public function isInstalledModule(array $module): bool {
        //return (int) $module['status'] & (ModuleManager::DISABLED | ModuleManager::ENABLED);
        return !empty($module['id']);
    }

    public function isEnabledModule(array $module): bool {
        return (int) $module['status'] & ModuleManager::ENABLED;
    }

    public function moduleNames(): array {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $modules = $this->db()->select('* FROM module ORDER BY weight, name')->rows();
        foreach ($moduleManager->uninstalledModuleNames() as $moduleName) {
            $modules[] = [
                'id' => null,
                'name' => $moduleName,
                'status' => ModuleManager::UNINSTALLED,
            ];
        }
        uasort($modules, function ($a, $b) {
            $res = strcmp($a['name'], $b['name']);
            if ($res === 0) {
                $weightA = $a['weight'] ?? 0;
                $weightB = $b['weight'] ?? 0;
                return $weightA - $weightB;
            }
            return $res;
        });

        return $modules;
    }

    protected function moduleDirPath(string $moduleName): string {
        return $this->serviceManager->get('moduleManager')->fs()->moduleDirPath($moduleName);
    }

    protected function writeModuleClass(string $moduleName, bool $isTheme) {
        $moduleName = classify($moduleName);
        $factory = new BuilderFactory;
        $nsBuilder = $factory->namespace($moduleName);
        if ($isTheme) {
            $nsBuilder
                ->addStmt($factory->use('Morpho\Web\Theme'))
                ->addStmt($factory->class('Module')->extend('Theme'));
        } else {
            $nsBuilder
                ->addStmt($factory->use('Morpho\Core\Module')->as('BaseModule'))
                ->addStmt($factory->class('Module')->extend('BaseModule'));
        }
        $stmts = [$nsBuilder->getNode()];
        $prettyPrinter = new PrettyPrinter\Standard();
        $moduleDirPath = $this->moduleDirPath($moduleName);
        if (is_dir($moduleDirPath)) {
            return $this->error('Directory exists');
        }
        $moduleFilePath = $moduleDirPath . '/Module.php';
        if (is_file($moduleFilePath)) {
            return $this->error('File exists');
        }
        File::write(
            $moduleFilePath,
            $prettyPrinter->prettyPrintFile($stmts)
        );
    }

    private function processModule(\Closure $process) {
        $moduleName = key(array_values($_POST)[0]);
        $process($this->serviceManager->get('moduleManager'), $moduleName);
        $this->serviceManager->get('router')->rebuildRoutes();
        $this->redirectToUri('/system/module/list');
    }
}
