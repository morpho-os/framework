<?php
namespace Morpho\Web\View;

use function Morpho\Base\{
    classify, htmlId, dasherize
};
use Morpho\Base\EmptyValueException;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use Morpho\Web\Uri;

class PhpTemplateEngine extends TemplateEngine implements IServiceManagerAware {
    protected $serviceManager;
    
    protected $tagRenderer;

    private $uri;

    private $request;

    const PLUGIN_SUFFIX = PLUGIN_SUFFIX;

    public function plugin($name, array $options = []) {
        $name = ucfirst($name);
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->createPlugin($name);
        }
        return $this->plugins[$name];
    }
    
    public function tag() {
        if (null === $this->tagRenderer) {
            $this->tagRenderer = new TagRenderer();
        }
        return $this->tagRenderer;
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
        return dasherize(self::moduleName()) . '-' . dasherize(self::controllerName()) . '-' . dasherize(self::actionName());
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
                $html .= '<option value="' . $this->escapeHtml($value) . '"' . $selected . '>' . $this->escapeHtml($text) . '</option>';
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
            $html .= '<option value="' . $this->escapeHtml($value) . '"' . $selected . '>' . $this->escapeHtml($text) . '</option>';
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
        $plugin = $this->plugin($pluginName);
        return $plugin(...$args);
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public static function escapeHtml($value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected function createPlugin(string $name) {
        $serviceManager = $this->serviceManager;
        $class = $serviceManager->get('moduleManager')
            ->child(self::moduleName())
            ->namespace()
            . '\\View\\Plugin\\'
                . classify(self::controllerName())
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