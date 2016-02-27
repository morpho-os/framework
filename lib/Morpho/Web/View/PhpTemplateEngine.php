<?php
namespace Morpho\Web\View;

use function Morpho\Base\{
    htmlId, camelize, dasherize
};
use Morpho\Base\EmptyValueException;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use Morpho\Web\Uri;

class PhpTemplateEngine extends TemplateEngine implements IServiceManagerAware {
    protected $serviceManager;

    private $uri;

    private $request;

    public function getPlugin(string $name) {
        $name = ucfirst($name);
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->createPlugin($name);
        }
        return $this->plugins[$name];
    }

    public function pageCssId(): string {
        return dasherize(self::moduleName()) . '-' . dasherize(self::controllerName()) . '-' . dasherize(self::actionName());
    }

    public function moduleName(): string {
        $moduleName = $this->request()->getModuleName();
        if (empty($moduleName)) {
            throw new EmptyValueException();
        }
        return $moduleName;
    }

    public function controllerName(): string {
        $controllerName = $this->request()->getControllerName();
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

    public function isUserLoggedIn(): bool {
        return $this->serviceManager->get('userManager')->isUserLoggedIn();
    }

    public function getLoggedInUser() {
        return $this->serviceManager->get('userManager')->getLoggedInUser();
    }

    public function uri(): Uri {
        if (null === $this->uri) {
            $this->uri = $this->request()->uri();
        }
        return $this->uri;
    }

    public function uriWithRedirectToSelf(string $uri): string {
        $currentUri = clone $this->uri();
        $currentUri->unsetQueryArg('redirect');
        $relativeRef = $currentUri->relativeRef();
        return $currentUri->parse($currentUri->prependWithBasePath($uri))
            ->appendQueryArgs(['redirect' => $relativeRef])
            ->__toString();
    }

    public function link(string $uri, string $text, array $attributes = [], array $options = null): string {
        $attributes['href'] = $this->uri()
            ->prependWithBasePath($uri);
        return TagRenderer::render('a', $attributes, $text, $options);
    }

    public function hiddenField(string $name, $value, array $attributes = null): string {
        return TagRenderer::renderSingle(
            'input',
            [
                'name'  => $name,
                'value' => $value,
                'type'  => 'hidden',
            ] + (array)$attributes
        );
    }

    public function options(array $options, $defaultValue = null): string {
        $html = '';
        $defaultValue = (string) $defaultValue;
        foreach ($options as $value => $text) {
            $value = (string) $value;
            $html .= '<option value="' . $this->escapeHtml($value) . '"' . ($value === $defaultValue ? ' selected' : '') . '>' . $this->escapeHtml($text) . '</option>';
        }
        return $html;
    }

    public function copyright(string $brand, $startYear = null): string {
        $currentYear = date('Y');
        if ($startYear == $currentYear) {
            $range = $currentYear;
        } else {
            $range = intval($startYear) . '-' . $currentYear;
        }
        return '© ' . $range . ', ' . $this->escapeHtml($brand);
    }

    public function __call($pluginName, array $args) {
        return call_user_func_array(
            $this->getPlugin($pluginName),
            $args
        );
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    protected static function escapeHtml($value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected function createPlugin(string $name) {
        $class = __NAMESPACE__ . '\\' . $name . 'Plugin';
        $plugin = new $class();
        if ($plugin instanceof IServiceManagerAware) {
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
