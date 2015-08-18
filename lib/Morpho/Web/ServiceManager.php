<?php
namespace Morpho\Web;

use Morpho\Core\ServiceManager as BaseServiceManager;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\HtmlParser;
use Morpho\Web\View\PhpTemplateEngine;

class ServiceManager extends BaseServiceManager {
    /*
    public function createRouterService()
    {
        if ($this->isFallbackMode()) {
            return new FallbackRouter();
        }
        return new Router($this->get('db'));
    }
    */
    public function createRouterService() {
        if ($this->isFallbackMode()) {
            return new FallbackRouter();
        }
        return new FastRouter();
    }

    protected function createSessionService() {
        return new Session(__CLASS__);
    }

    protected function createRequestService() {
        return new Request();
    }

    protected function createTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine();
        $templateEngine->setCacheDirPath($this->get('siteManager')->getCurrentSite()->getCacheDirPath());
        $templateEngine->useCache($templateEngineConfig['useCache']);
        $templateEngine->attach(new Compiler())
            ->attach(new HtmlParser($this));
        return $templateEngine;
    }

    protected function createMessengerService() {
        return new Messenger();
    }

    protected function createAccessManagerService() {
        return new AccessManager($this->get('session'), $this->get('db'));
    }

    protected function createModuleManagerService() {
        $this->get('moduleAutoloader')->register();
        $moduleManager = new ModuleManager($this->get('db'));
        $moduleManager->isFallbackMode($this->isFallbackMode());
        return $moduleManager;
    }

    protected function isFallbackMode() {
        return $this->get('siteManager')->isFallbackMode();
    }
}
