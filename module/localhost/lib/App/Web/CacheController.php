<?php declare(strict_types=1);
namespace Morpho\Site\Localhost\App\Web;

use Morpho\Fs\Dir;
use Morpho\App\Web\Controller;

class CacheController extends Controller {
    public function clearAllAction() {
        $cacheDirPath = $this->serviceManager['moduleIndex']->module($this->serviceManager['site']->moduleName())->cacheDirPath();
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
        // @TODO: CSRF
        return $this->mkRedirectResult($this->query('redirect') ?: '/')
            ->withSuccessMessage("The cache has been cleared successfully");
    }

    /**
     * @Title Rebuild routes
     */
    public function rebuildRoutesAction() {
        $this->serviceManager['router']->rebuildRoutes();
        // @TODO: CSRF
        return $this->mkRedirectResult($this->query('redirect') ?: '/')
            ->withSuccessMessage("Routes have been rebuilt successfully");
    }
    /**
     * @Title Rebuild events
    public function rebuildEventsAction() {
        $this->serviceManager['moduleManager']->rebuildEvents();
        $this->redirectToHome("Events were rebuilt successfully");
    }
    */
}
