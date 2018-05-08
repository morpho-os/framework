<?php declare(strict_types=1);
namespace Morpho\System\App\Web;

use Morpho\Fs\Dir;
use Morpho\App\Web\Controller;

class CacheController extends Controller {
    public function clearAllAction() {
        $cacheDirPath = $this->serviceManager['moduleIndex']->moduleMeta($this->serviceManager['site']->moduleName())->cacheDirPath();
        $gitignoreFileExists = \is_file($cacheDirPath . '/.gitignore');
        Dir::delete($cacheDirPath, function (string $path, $isDir) use ($cacheDirPath, $gitignoreFileExists) {
            if ($isDir) {
                return $path !== $cacheDirPath;
            } else {
                if (!$gitignoreFileExists) {
                    return true;
                }
                return $path !== $cacheDirPath . '/.gitignore';
            }
        });
        $this->messenger()->addSuccessMessage("The cache has been cleared successfully");
        // @TODO: CSRF
        return $this->redirect($this->query('redirect') ?: '/');
    }

    /**
     * @Title Rebuild routes
     */
    public function rebuildRoutesAction() {
        $this->serviceManager['router']->rebuildRoutes();
        $this->messenger()->addSuccessMessage("Routes have been rebuilt successfully");
        // @TODO: CSRF
        $this->redirect($this->query('redirect') ?: '/');
    }
    /**
     * @Title Rebuild events
    public function rebuildEventsAction() {
        $this->serviceManager['moduleManager']->rebuildEvents();
        $this->redirectToHome("Events were rebuilt successfully");
    }
    */
}
