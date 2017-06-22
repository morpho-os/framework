<?php
namespace Morpho\System\Controller;

use Morpho\Fs\Directory;
use Morpho\Web\Controller;

class CacheController extends Controller {
    public function clearAllAction() {
        $cacheDirPath = $this->serviceManager->get('site')->cacheDirPath();
        $gitIgnoreFileExists = is_file($cacheDirPath . '/.gitignore');
        Directory::delete($cacheDirPath, function (string $path, $isDir) use ($cacheDirPath, $gitIgnoreFileExists) {
            if ($isDir) {
                return $path !== $cacheDirPath;
            } else {
                if (!$gitIgnoreFileExists) {
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
        $this->redirectToHome("Routes were rebuilt successfully");
    }

    /**
     * @Title Rebuild events
     */
    public function rebuildEventsAction() {
        $this->serviceManager->get('moduleManager')->rebuildEvents();
        $this->redirectToHome("Events were rebuilt successfully");
    }
}