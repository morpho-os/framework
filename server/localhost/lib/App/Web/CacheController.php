<?php declare(strict_types=1);
namespace Morpho\Site\Localhost\App\Web;

use Morpho\Fs\Dir;
use Morpho\App\Web\Controller;

class CacheController extends Controller {
    public function clearAll() {
        $cacheDirPath = $this->serviceManager['serverModuleIndex']->module($this->serviceManager['site']->moduleName())->cacheDirPath();
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
        return $this->mkRedirectResult($this->query('redirect') ?: '/')
            ->withSuccessMessage("The cache has been cleared successfully");
    }

    public function clearRoutes() {
        $this->serviceManager['router']->rebuildRoutes();
        return $this->mkRedirectResult($this->query('redirect') ?: '/')
            ->withSuccessMessage("Routes have been rebuilt successfully");
    }
    /**
     * @Title Rebuild events
    public function rebuildEvents() {
        $this->serviceManager['moduleManager']->rebuildEvents();
        $this->redirectToHome("Events were rebuilt successfully");
    }
    */
}
