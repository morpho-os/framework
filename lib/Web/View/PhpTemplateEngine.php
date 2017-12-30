<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\{
    classify, dasherize, last
};
use Morpho\Base\EmptyValueException;
use const Morpho\Core\PLUGIN_SUFFIX;
use Morpho\Ioc\IServiceManager;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Web\Controller;
use function Morpho\Web\prependBasePath;
use Morpho\Web\Uri\Uri;

class PhpTemplateEngine extends TemplateEngine implements IHasServiceManager {
    protected $serviceManager;
    
    private $uri;

    private $request;

    private $plugins;

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

    public function controller(): Controller {
        [$moduleName, $controllerName, ] = $this->request()->handler();
        $module = $this->serviceManager->get('moduleProvider')->offsetGet($moduleName);
        return $module->offsetGet($controllerName);
    }

    public function moduleName(): string {
        $moduleName = $this->request()->moduleName();
        if (empty($moduleName)) {
            throw new EmptyValueException();
        }
        return $moduleName;
    }

    public function controllerName(): string {
        $controllerName = $this->request()->controllerName();
        if (empty($controllerName)) {
            throw new EmptyValueException();
        }
        return $controllerName;
    }

    public function actionName(): string {
        $actionName = $this->request()->actionName();
        if (empty($actionName)) {
            throw new EmptyValueException();
        }
        return $actionName;
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
        $serviceManager = $this->serviceManager;
        $module = $serviceManager->get('moduleProvider')->offsetGet($this->moduleName());
        $class = $module->namespace()
            . '\\' . classify(self::controllerName())
            . '\\View'
            . '\\' . $name . self::PLUGIN_SUFFIX;
        if (!class_exists($class)) {
            $class1 = __NAMESPACE__ . '\\' . $name . self::PLUGIN_SUFFIX;
            if (!class_exists($class1)) {
                throw new \RuntimeException("Unable to find either '$class' or '$class1' plugin class");
            }
            $plugin = new $class1();
        } else {
            $plugin = new $class();
        }
        if ($plugin instanceof IHasServiceManager) {
            $plugin->setServiceManager($this->serviceManager);
        }
        return $plugin;
    }

    protected function request() {
        if (null === $this->request) {
            $this->request = $this->serviceManager->get('request');
        }
        return $this->request;
    }
}