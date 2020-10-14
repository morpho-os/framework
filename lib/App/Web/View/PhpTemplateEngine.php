<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\App\Web\Request;
use Morpho\App\Web\Uri\Uri;
use Morpho\Base\Conf;
use Morpho\Ioc\{IHasServiceManager, IServiceManager};
use function Morpho\App\Web\prependBasePath;
use function Morpho\Base\{toJson, dasherize, deleteDups};

class PhpTemplateEngine extends TemplateEngine {
    protected IServiceManager $serviceManager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var callable[]
     */
    protected $tasks;

    private ?Uri $uri = null;

    private array $plugins = [];

    private $pluginResolver;

    public function __construct(IServiceManager $serviceManager) {
        parent::__construct();
        $this->setServiceManager($serviceManager);
        $this->init();
    }

    public function plugin($name) {
        $name = \ucfirst($name);
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->mkPlugin($name);
        }
        return $this->plugins[$name];
    }

    public function pageCssId(): string {
        $handler = $this->request->handler();
        return dasherize($handler['controllerPath']) . '-' . dasherize($handler['method']);
    }

    public function jsConf(): \ArrayObject {
        if (!isset($this->request['jsConf'])) {
            $this->request['jsConf'] = new \ArrayObject();
        }
        return $this->request['jsConf'];
    }

    public function handler() {
        return $this->request->handler()['instance'];
    }

/*    public function isUserLoggedIn(): bool {
        return $this->serviceManager['userManager']->isUserLoggedIn();
    }

    public function loggedInUser() {
        return $this->serviceManager['userManager']->loggedInUser();
    }*/

    public function toJson($val): string {
        return toJson($val);
    }

    public function uri(): Uri {
        if (null === $this->uri) {
            $this->uri = $this->request->uri();
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
        if (\is_string($uri)) {
            $uri = new Uri($uri);
        }
        $newUri = prependBasePath(function () {
            return $this->uri()->path()->basePath();
        }, $uri);
        $newUri->query()['redirect'] = $this->uri()->toStr(null, false);
        return $newUri->toStr(null, true);
    }

    /**
     * Renders link - HTML `a` tag.
     * @param string|Uri $uri
     */
    public function l($uri, string $text, array $attributes = null, array $conf = null): string {
        $attributes = (array) $attributes;
        $attributes['href'] = prependBasePath(function () { return $this->uri()->path()->basePath(); }, $uri)->toStr(null, false);
        return $this->tag('a', $attributes, $text, $conf);
    }

    public function copyright(string $brand, $startYear = null): string {
        $currentYear = date('Y');
        if ($startYear == $currentYear) {
            $range = $currentYear;
        } else {
            $range = intval($startYear) . '-' . $currentYear;
        }
        return '© ' . $range . ', ' . $this->encode($brand);
    }

    public function __call($pluginName, array $args) {
        $plugin = $this->plugin($pluginName);
        return $plugin($args);
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
        $this->request = $this->serviceManager['request'];
        $this->uri = null;
    }

    public function getIterator(): iterable {
        return new \ArrayIterator([
            new Processor(),
            function ($context) {
                $code = $context['code'];
                foreach ($this->tasks as $task) {
                    $code = $task($code);
                }
                $context['code'] = $code;
                return $context;
            }
        ]);
    }

    public function id(string $id): string {
        static $htmlIds = [];
        $id = dasherize(deleteDups(\preg_replace('/[^\w-]/s', '-', (string)$id), '-'));
        if (isset($htmlIds[$id])) {
            $id .= '-' . $htmlIds[$id]++;
        } else {
            $htmlIds[$id] = 1;
        }
        return $this->encode($id);
    }

    public static function encode($text): string {
        return \htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Inverts result that can be obtained with escapeHtml().
     */
    public static function decode($text): string {
        return \htmlspecialchars_decode($text, ENT_QUOTES);
    }

    public function openTag(string $tagName, array $attributes = [], bool $isXml = false): string {
        return '<'
            . $this->encode($tagName)
            . $this->attributes($attributes)
            . ($isXml ? ' />' : '>');
    }

    public function closeTag(string $name): string {
        return '</' . $this->encode($name) . '>';
    }

    /**
     * The source was found in Drupal-7.
     */
    public function attributes(array $attributes): string {
        foreach ($attributes as $attribute => &$data) {
            if (!\is_numeric($attribute)) {
                $data = \implode(' ', (array)$data);
                $data = $attribute . '="' . $this->encode($data) . '"';
            }
        }

        return $attributes ? ' ' . \implode(' ', $attributes) : '';
    }

    public function singleTag(string $tagName, array $attributes = null, array $conf = []): string {
        $conf['isSingle'] = true;
        return $this->tag($tagName, $attributes, null, $conf);
    }

    public function tag(string $tagName, array $attributes = null, string $text = null, array $conf = null): string {
        $conf = Conf::check(
            [
                'escapeText' => true,
                'isSingle'   => false,
                'isXml'      => false,
                'eol'        => false,
            ],
            (array)$conf
        );
        $output = $this->openTag($tagName, (array)$attributes, $conf['isXml']);
        if (!$conf['isSingle']) {
            $output .= $conf['escapeText'] ? $this->encode($text) : $text;
            $output .= $this->closeTag($tagName);
        }
        if ($conf['eol']) {
            $output .= "\n";
        }
        return $output;
    }

    public function select(array $attributes = null, $options): string {
        $attributes = (array) $attributes;
        if (!isset($attributes['id']) && isset($attributes['name'])) {
            $attributes['id'] = $this->id($attributes['name']);
        }
        $html = $this->openTag('select', $attributes);
        $html .= $this->options($options, ['value' => null] + $attributes);
        $html .= '</select>';
        return $html;
    }

    /**
     * @param array|\Traversable $options
     * @param array|\Traversable|scalar|null $selectedOption
     */
    public function options($options, $selectedOption = null): string {
        $html = '';
        if (null === $selectedOption || \is_scalar($selectedOption)) {
            $defaultValue = (string) $selectedOption;
            foreach ($options as $value => $text) {
                $value = (string) $value;
                $selected = $value === $defaultValue ? ' selected' : '';
                $html .= '<option value="' . $this->encode($value) . $selected . '">' . $this->encode($text) . '</option>';
            }
            return $html;
        }
        if (!\is_array($selectedOption) && !$selectedOption instanceof \Traversable) {
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
            $html .= '<option value="' . $this->encode($value) . $selected . '">' . $this->encode($text) . '</option>';
        }
        return $html;
    }

    public function hidden(string $name, $value, array $attributes = null): string {
        $attributes = [
            'name'  => $name,
            'value' => $value,
            'type' => 'hidden',
        ] + (array)$attributes;
        if (!isset($attributes['id'])) {
            $attributes['id'] = $this->id($attributes['name']);
        }
        return $this->singleTag('input', $attributes);
    }

    public function httpMethodField(string $method = null, array $attributes = null): string {
        return $this->hidden('_method', $method, $attributes);
    }

    protected function mkPlugin(string $name) {
        if (!$this->pluginResolver) {
            $this->pluginResolver = $this->serviceManager['pluginResolver'];
        }
        $class = ($this->pluginResolver)($name);
        $plugin = new $class();
        if ($plugin instanceof IHasServiceManager) {
            $plugin->setServiceManager($this->serviceManager);
        }
        return $plugin;
    }

    protected function init(): void {
        $serviceManager = $this->serviceManager;
        $this->tasks = [
            new FormPersister($serviceManager),
            new UriProcessor($serviceManager),
            new ScriptProcessor($serviceManager),
        ];
    }
}
