<?php
namespace Morpho\Web\View;

use function Morpho\Base\{htmlId, camelize};
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

class PhpTemplateEngine extends TemplateEngine implements IServiceManagerAware {
    protected $serviceManager;

    public function getPlugin($name) {
        $name = ucfirst($name);
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->createPlugin($name);
        }
        return $this->plugins[$name];
    }

    public function hiddenField($name, $value, array $attributes = null) {
        return TagRenderer::renderSingle(
            'input',
            [
                'name' => $name,
                'value' => $value,
                'type' => 'hidden',
            ] + (array)$attributes
        );
    }

    public function link(string $uri, string $text, array $attributes = []): string {
        $attributes['href'] = $this->serviceManager->get('request')->prependUriWithBasePath($uri);
        return TagRenderer::render('a', $attributes, $text);
    }

    public function copyright($brand, $startYear = null) {
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

    protected function escapeHtml($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected function createPlugin($name) {
        $class = __NAMESPACE__ . '\\' . $name . 'Plugin';
        $plugin = new $class();
        if ($plugin instanceof IServiceManagerAware) {
            $plugin->setServiceManager($this->serviceManager);
        }
        return $plugin;
    }
}
