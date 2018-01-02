<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\{
    dasherize, last
};
use const Morpho\Core\PLUGIN_SUFFIX;
use Morpho\Ioc\IServiceManager;
use Morpho\Ioc\IHasServiceManager;
use function Morpho\Web\prependBasePath;
use Morpho\Web\Request;
use Morpho\Web\Uri\Uri;

class PhpTemplateEngine extends TemplateEngine implements IHasServiceManager {
    /**
     * @var IServiceManager
     */
    protected $serviceManager;
    
    private $uri;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $plugins = [];

    private const PLUGIN_SUFFIX = PLUGIN_SUFFIX;

    public function plugin($name) {
        $name = ucfirst($name);
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->newPlugin($name);
        }
        return $this->plugins[$name];
    }

    public function pageCssId(): string {
        return dasherize(last($this->moduleName(), '/'))
            . '-' . dasherize($this->controllerName())
            . '-' . dasherize($this->actionName());
    }

    public function handler(): callable {
        return $this->request()->params()['handlerFn'];
    }

    public function moduleName(): ?string {
        return $this->request()->moduleName();
    }

    public function controllerName(): ?string {
        return $this->request()->controllerName();
    }

    public function actionName(): ?string {
        return $this->request()->actionName();
    }

/*    public function isUserLoggedIn(): bool {
        return $this->serviceManager->get('userManager')->isUserLoggedIn();
    }

    public function loggedInUser() {
        return $this->serviceManager->get('userManager')->loggedInUser();
    }*/

    public function uri(): Uri {
        if (null === $this->uri) {
            $this->uri = $this->request()->uri();
        }
        return $this->uri;
    }

    /**
     * For the $uri === 'http://foo/bar' adds the query argument redirect=$currentPageUri
     * i.e. returns Uri which will redirect to the current page.
     * E.g.: if the current URI === 'http://baz/' then the call
     *     $templateEngine->uriWithRedirectToSelf('http://foo/bar')
     * will return 'http://foo/bar?redirect=http://baz
     */
    public function uriWithRedirectToSelf($uri): string {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }
        $newUri = prependBasePath(function () {
            return $this->uri()->path()->basePath();
        }, $uri);
        $newUri->query()['redirect'] = $this->uri()->toStr(false);
        return $newUri->toStr(true);
    }

    /**
     * @param string|Uri $uri
     */
    public function link($uri, string $text, array $attributes = null, array $options = null): string {
        $attributes = (array) $attributes;
        $attributes['href'] = prependBasePath(function () { return $this->uri()->path()->basePath(); }, $uri)->toStr(false);
        return Html::tag('a', $attributes, $text, $options);
    }

    public function __call($pluginName, array $args) {
        $plugin = $this->plugin($pluginName);
        return $plugin($args);
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
        $this->request = $this->uri = null;
    }

    protected function newPlugin(string $name) {
        $moduleName = $this->request()->moduleName();

        $serviceManager = $this->serviceManager;
        $moduleMeta = $serviceManager->get('moduleIndex')->moduleMeta($moduleName);
        $instanceProvider = $serviceManager->get('instanceProvider');
        $classFilePath = $instanceProvider->classFilePath($moduleMeta, 'Web\\View\\' . $name . self::PLUGIN_SUFFIX);
        if (false === $classFilePath) {
            $class = __NAMESPACE__ . '\\' . $name . self::PLUGIN_SUFFIX;
            if (!class_exists($class)) {
                throw new \RuntimeException("Unable to find the plugin '$name'");
            }
        } else {
            require_once $classFilePath[1];
            $class = $classFilePath[0];
        }
        $plugin = new $class();
        if ($plugin instanceof IHasServiceManager) {
            $plugin->setServiceManager($this->serviceManager);
        }
        return $plugin;
    }

    protected function request(): Request {
        if (null === $this->request) {
            $this->request = $this->serviceManager->get('request');
        }
        return $this->request;
    }
}