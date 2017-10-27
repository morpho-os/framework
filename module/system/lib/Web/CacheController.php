<?php
namespace Morpho\System\Web;

use Morpho\Fs\Directory;
use Morpho\Web\Controller;

class CacheController extends Controller {
    public function clearAllAction() {
        $cacheDirPath = $this->serviceManager->get('moduleIndex')->moduleMeta($this->serviceManager->get('site')->moduleName())->cacheDirPath();
        $gitignoreFileExists = is_file($cacheDirPath . '/.gitignore');
        Directory::delete($cacheDirPath, function (string $path, $isDir) use ($cacheDirPath, $gitignoreFileExists) {
            if ($isDir) {
                return $path !== $cacheDirPath;
            } else {
                if (!$gitignoreFileExists) {
                    return true;
                }
                return $path !== $cacheDirPath . '/.gitignore';
            }
        });
        $this->redirectToHome("The cache has been cleared successfully");
    }

    /**
     * @Title Rebuild routes
     */
    public function rebuildRoutesAction() {
        $this->serviceManager->get('router')->rebuildRoutes();
        $this->redirectToHome("Routes have been rebuilt successfully");
    }

    /**
     * @Title Rebuild events
    public function rebuildEventsAction() {
        $this->serviceManager->get('moduleManager')->rebuildEvents();
        $this->redirectToHome("Events were rebuilt successfully");
    }
    */
}