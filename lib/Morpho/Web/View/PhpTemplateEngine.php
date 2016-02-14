<?php
namespace Morpho\Web\View;

use function Morpho\Base\{
    htmlId, camelize
};
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use Morpho\Web\Uri;

class PhpTemplateEngine extends TemplateEngine implements IServiceManagerAware {
    protected $serviceManager;

    private $uri;

    public function getPlugin(string $name) {
        $name = ucfirst($name);
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->createPlugin($name);
        }
        return $this->plugins[$name];
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
        foreach ($options as $value => $text) {
            $html .= '<option value="' . $this->escapeHtml($value) . '"' . ($value === $defaultValue ? ' selected' : '') . '>' . $this->escapeHtml($text) . '</option>';
        }
        return $html;
    }

    public function isUserLoggedIn(): bool {
        return $this->serviceManager->get('userManager')->isUserLoggedIn();
    }

    public function getLoggedInUser() {
        return $this->serviceManager->get('userManager')->getLoggedInUser();
    }

    public function uri(): Uri {
        if (null === $this->uri) {
            $this->uri = $this->serviceManager->get('request')->uri();
        }
        return $this->uri;
    }

    public function uriWithRedirectToSelf( $uri): string {
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
}
