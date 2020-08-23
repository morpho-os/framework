<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use function Morpho\Base\dasherize;
use function Morpho\Base\toJson;
use Morpho\Ioc\IHasServiceManager;
use function Morpho\App\Web\prependBasePath;
use Morpho\Ioc\IServiceManager;
use Morpho\App\Web\Request;
use Morpho\App\Web\Uri\Uri;

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
     * @param string|Uri $uri
     */
    public function link($uri, string $text, array $attributes = null, array $conf = null): string {
        $attributes = (array) $attributes;
        $attributes['href'] = prependBasePath(function () { return $this->uri()->path()->basePath(); }, $uri)->toStr(null, false);
        return Html::tag('a', $attributes, $text, $conf);
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

    public function e($s): string {
        return Html::encode($s);
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
