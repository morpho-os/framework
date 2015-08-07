<?php
namespace Morpho\Web\View;

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

    public function textField($name, $value = null, array $attributes = null) {
        $attributes = (array)$attributes;
        $attributes += [
            'class' => 'form-control',
        ];
        return TagRenderer::renderSingle(
            'input',
            [
                'name' => $name,
                'value' => $value,
                'type' => 'text',
            ] + $attributes
        );
    }

    public function submitButton($text, $name = null, $value = null, array $attributes = null) {
        return $this->button($text, $name, $value, ['type' => 'submit'] + (array)$attributes);
    }

    public function button($text, $name = null, $value = null, array $attributes = null) {
        if (null === $name) {
            $name = camelize($text);
        }
        $attributes = (array)$attributes;
        $attributes['name'] = $name;
        if (null !== $value) {
            $attributes['value'] = $value;
        }

        $attributes += ['type' => 'button'];

        $attributes['class'] = 'btn btn-default';

        if (!isset($attributes['id'])) {
            $attributes['id'] = htmlId($name);
        }

        return TagRenderer::render('button', $attributes, $text);
    }

    public function link($uri, $text, array $attributes = [], array $args = null, array $options = []) {
        $attributes['href'] = $this->serviceManager->get('request')->getRelativeUri($uri, null, $args, $options);
        return TagRenderer::render('a', $attributes, $text);
    }

    public function uriWithRedirectToSelf($relativeUri) {
        return $this->uriWithRedirectTo(
            $relativeUri,
            $this->serviceManager->get('request')->getRelativeUri()
        );
    }

    public function uriWithRedirectTo($relativeUri, $redirectToUri) {
        return $this->serviceManager
            ->get('request')
            ->getRelativeUri($relativeUri, null, ['redirect' => $redirectToUri]);
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
