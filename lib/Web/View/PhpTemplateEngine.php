<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\{
    classify, htmlId, dasherize, last
};
use Morpho\Base\EmptyValueException;
use const Morpho\Core\PLUGIN_SUFFIX;
use Morpho\Di\IServiceManager;
use Morpho\Di\IHasServiceManager;
use Morpho\Web\Controller;
use Morpho\Web\Uri;

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
    
    public static function formatFloat($val) {
        if (empty($val)) {
            $val = 0;
        }
        $val = str_replace(',', '.', $val);
        return number_format(round(floatval($val), 2), 2, '.', ' ');
    }

    public function htmlId($id): string {
        return htmlId($id);
    }

    public function pageCssId(): string {
        return dasherize(last(self::moduleName(), '/'))
            . '-' . dasherize(self::controllerName())
            . '-' . dasherize(self::actionName());
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

    public function isUserLoggedIn(): bool {
        return $this->serviceManager->get('userManager')->isUserLoggedIn();
    }

    public function loggedInUser() {
        return $this->serviceManager->get('userManager')->loggedInUser();
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
        return $currentUri->parse($currentUri->prependBasePath($uri))
            ->appendQueryArgs(['redirect' => $relativeRef])
            ->__toString();
    }

    public function link(string $uri, string $text, array $attributes = [], array $options = null): string {
        $attributes['href'] = $this->uri()
            ->prependBasePath($uri);
        return Html::tag('a', $attributes, $text, $options);
    }

    public function hiddenField(string $name, $value, array $attributes = null): string {
        return Html::singleTag(
            'input',
            [
                'name'  => $name,
                'value' => $value,
                'type'  => 'hidden',
            ] + (array)$attributes
        );
    }
    
    public function httpMethodHiddenField(string $method = null, array $attributes = null): string {
        return $this->hiddenField('_method', $method, $attributes);
    }

    /**
     * @param array|\Traversable $options
     * @param array|\Traversable|scalar|null $selectedOption
     */
    public function options($options, $selectedOption = null): string {
        $html = '';
        if (null === $selectedOption || is_scalar($selectedOption)) {
            $defaultValue = (string) $selectedOption;
            foreach ($options as $value => $text) {
                $value = (string) $value;
                $selected = $value === $defaultValue ? ' selected' : '';
                $html .= '<option value="' . Html::encode($value) . '"' . $selected . '>' . Html::encode($text) . '</option>';
            }
            return $html;
        }
        if (!is_array($selectedOption) && !$selectedOption instanceof \Traversable) {
            throw new \UnexpectedValueException();
        }
        $newOptions = [];
        foreach ($options as $value => $text) {
            $newOptions[(string) $value] = $text;
        }
        $selectedOptions = [];
        foreach ($selectedOption as $val) {
            $val = (string) $val;
            $selectedOptions[$val] = true;
        }
        foreach ($newOptions as $value => $text) {
            $selected = isset($selectedOptions[$value]) ? ' selected' : '';
            $html .= '<option value="' . Html::encode($value) . '"' . $selected . '>' . Html::encode($text) . '</option>';
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
        return '© ' . $range . ', ' . Html::encode($brand);
    }

    public function __call($pluginName, array $args) {
        $plugin = $this->plugin($pluginName);
        return $plugin($args);
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
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