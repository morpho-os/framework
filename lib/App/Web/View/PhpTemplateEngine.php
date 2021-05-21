<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use ArrayObject;
use Morpho\App\Web\IRequest;
use Morpho\App\Web\Uri\Uri;
use Morpho\Base\ArrPipe;
use Morpho\Base\Conf;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use RuntimeException;
use Throwable;
use Traversable;
use UnexpectedValueException;

use function extract;
use function htmlspecialchars;
use function htmlspecialchars_decode;
use function implode;
use function is_array;
use function is_numeric;
use function is_scalar;
use function Morpho\Base\dasherize;
use function Morpho\Base\deleteDups;
use function Morpho\Base\toJson;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function preg_replace;
use function trim;
use function ucfirst;

class PhpTemplateEngine extends ArrPipe {
    //public const HIDDEN_FIELD = 'hidden';

    public const VIEW_FILE_EXT = '.phtml';
    private static array $htmlIds = [];

    protected bool $forceCompile;
    protected array $baseSourceDirPaths = [];
    protected string $targetDirPath;

    private array $plugins = [];

    /**
     * @var callable
     */
    private $pluginFactory;

    private IRequest $request;

    private ?Uri $uri = null;

    //protected array $vars = [];

    public function __construct(array $conf = null) {
        $this->init();
        $conf = (array) $conf;
        $this->forceCompile = $conf['forceCompile'] ?? false;
        $this->pluginFactory = $conf['pluginFactory'] ?? function () {
            };
        $this->request = $conf['request'];
        if (!isset($conf['phases'])) {
            $conf['phases'] = self::mkDefaultPhases($conf);
        }
        parent::__construct($conf['phases']);
    }

    public function setRequest(IRequest $request): void {
        $this->request = $request;
    }

    public function request(): IRequest {
        return $this->request;
    }

    public function instance(): mixed {
        return $this['request']->handler()['instance'];
    }

    public static function mkDefaultPhases(array $conf): array {
        return [
            'phpProcessor'    => new PhpProcessor(),
            'formPersister'   => new FormPersister($conf['request']),
            'uriProcessor'    => new UriProcessor($conf['request']),
            'scriptProcessor' => new ScriptProcessor($conf['request'], $conf['site']),
        ];
    }

    /*
    public function __set(string $varName, $value): void {
        $this->vars[$varName] = $value;
    }

    public function __get(string $varName) {
        if (!isset($this->vars[$varName])) {
            throw new RuntimeException("The template variable '$varName' was not set.");
        }
        return $this->vars[$varName];
    }

    public function __isset(string $varName): bool {
        return isset($this->vars[$varName]);
    }

    public function __unset(string $name): void {
        unset($this->vars[$name]);
    }

    public function mergeVars(array $vars): void {
        $this->vars = array_merge($this->vars, $vars);
    }

    public function setVars(array $vars): void {
        $this->vars = $vars;
    }

    public function vars(): array {
        return $this->vars;
    }

    public function isUserLoggedIn(): bool {
        return $this->serviceManager['userManager']->isUserLoggedIn();
    }

    public function loggedInUser() {
        return $this->serviceManager['userManager']->loggedInUser();
    }
*/

    public function forceCompile(bool $flag = null): bool {
        if (null !== $flag) {
            return $this->forceCompile = $flag;
        }
        return $this->forceCompile;
    }

    public function setBaseTargetDirPath(string $dirPath): void {
        $this->targetDirPath = $dirPath;
    }

    public function addBaseSourceDirPath(string $dirPath): self {
        $baseDirPaths = $this->baseSourceDirPaths;
        $key = array_search($dirPath, $baseDirPaths);
        if (false !== $key) {
            unset($baseDirPaths[$key]);
        }
        $baseDirPaths[] = $dirPath;
        $this->baseSourceDirPaths = array_values($baseDirPaths);
        return $this;
    }

    public function baseSourceDirPaths(): array {
        return $this->baseSourceDirPaths;
    }

    public function clearBaseSourceDirPaths(): void {
        $this->baseSourceDirPaths = [];
    }

    /**
     * Translates PHTML code into PHP code and evaluates the PHP code by exporting variables from the $context.
     */
    public function __invoke(mixed $context): string {
        $sourceAbsFilePath = $this->sourceAbsFilePath($context['_view']);
        $targetAbsFilePath = $this->targetDirPath . '/' . $context['_view'] . '.php';
        $this->compileFile($sourceAbsFilePath, $targetAbsFilePath, []);
        return $this->evalPhpFile($targetAbsFilePath, $context);
    }

    /**
     * Evaluates PHPTemplateEngine code.
     * @param string $sourceCode
     * @param array $__vars
     * @return string
     */
    public function eval(string $sourceCode, array $__vars): string {
        // 1. Compile to PHP
        $__code = $this->compile($sourceCode);
        unset($sourceCode);
        extract($__vars, EXTR_SKIP);
        unset($__vars);
        ob_start();
        try {
            eval('?>' . $__code);
        } catch (Throwable $e) {
            // Don't output any result in case of Error
            ob_end_clean();
            throw $e;
        }
        return trim(ob_get_clean());
    }

    /**
     * Evaluates a file containing PHPTemplateEngine code by compiling it to PHP and evaluating the produced PHP. The result of evaluation is returned as string.
     * @param string $sourceAbsFilePath
     * @param array|null $context
     * @return string
     * @throws Throwable
     */
    public function evalFile(string $sourceAbsFilePath, array $context = null): string {
        $candidateDirPaths = [];
        for ($i = count($this->baseSourceDirPaths) - 1; $i >= 0; $i--) {
            $baseSourceDirPath = $this->baseSourceDirPaths[$i];
            if (str_starts_with($sourceAbsFilePath, $baseSourceDirPath)) {
                $candidateDirPaths[] = $baseSourceDirPath;
            }
        }
        if (!$candidateDirPaths) {
            throw new UnexpectedValueException("Unable to find a base directory for the file " . $sourceAbsFilePath);
        }
        $max = [0, strlen($candidateDirPaths[0])];
        for ($i = 1, $n = count($candidateDirPaths); $i < $n; $i++) {
            $candidateDirPath = $candidateDirPaths[$i];
            $length = strlen($candidateDirPath);
            if ($length > $max[1]) {
                $max = [$i, $length];
            }
        }
        $baseSourceDirPath = $candidateDirPaths[$max[0]];
        $targetRelFilePath = Path::changeExt(Path::rel($sourceAbsFilePath, $baseSourceDirPath), 'php');
        $targetAbsFilePath = $this->targetDirPath . '/' . $targetRelFilePath;
        $this->compileFile($sourceAbsFilePath, $targetAbsFilePath, []);
        return $this->evalPhpFile($targetAbsFilePath, (array) $context);
    }

    protected function compile(string $sourceCode): string {
        $context = parent::__invoke(['program' => $sourceCode]);
        return $context['program'];
    }

    protected function compileFile(string $sourceFilePath, string $targetFilePath, array $context): void {
        $forceCompile = $this->forceCompile;
        if ($forceCompile || !file_exists($targetFilePath)) {
            $context['filePath'] = $sourceFilePath;
            $context['program'] = file_get_contents($sourceFilePath);
            $preprocessed = parent::__invoke($context);
            File::write($targetFilePath, $preprocessed['program']);
        }
    }

    /**
     * Evaluates PHP from the passed PHP file making elements of the $__vars be accessible as PHP variables for code in it.
     */
    public function evalPhpFile(string $__phpFilePath, array $__vars): string {
        // NB: We can't use the Base\tpl() function here as we need to preserve $this
        extract($__vars, EXTR_SKIP);
        unset($__vars);
        ob_start();
        try {
            require $__phpFilePath;
        } catch (Throwable $e) {
            // Don't output any result in case of Error
            ob_end_clean();
            throw $e;
        }
        return trim(ob_get_clean());
    }

    public function htmlId(string $htmlId): string {
        $htmlId = dasherize(deleteDups(preg_replace('/[^\w-]/s', '-', (string) $htmlId), '-'));
        if (isset(self::$htmlIds[$htmlId])) {
            $htmlId .= '-' . self::$htmlIds[$htmlId]++;
        } else {
            self::$htmlIds[$htmlId] = 1;
        }
        return $this->e($htmlId);
    }

    public function pageHtmlId(): string {
        $handler = $this->request->handler();
        return $this->htmlId(str_replace('/', '-', $handler['controllerPath'])) . '-' . dasherize($handler['method']);
    }

    public function textField(array $attribs): string {
        $attribs['type'] = 'text';
        return $this->formEl($this->tag1('input', $this->addCommonAttribs($attribs)));
    }

    public function textareaField(array $attribs): string {
        $val = $attribs['value'];
        unset($attribs['value']);
        return $this->formEl($this->tag('textarea', $val, $this->addCommonAttribs($attribs)));
    }

    public function hiddenField(array $attribs): string {
        $attribs['type'] = 'hidden';
        return $this->formEl($this->tag1('input', $this->addCommonAttribs($attribs)));
    }

    public function checkboxField(array $attribs): string {
        $attribs['type'] = 'checkbox';
        if (!isset($attribs['value'])) {
            $attribs['value'] = 1;
        }
        return $this->formEl($this->tag1('input', $this->addCommonAttribs($attribs)));
    }

    public function selectField(?iterable $options, mixed $selectedOption = null, array $attribs = null): string {
        $html = $this->openTag('select', $this->addCommonAttribs((array) $attribs));
        if (null !== $options) {
            $html .= $this->optionFields($options, $selectedOption);
        }
        $html .= '</select>';
        return $this->formEl($html);
    }

    public function optionFields(iterable $options, mixed $selectedOption = null): string {
        $html = '';
        if (null === $selectedOption || is_scalar($selectedOption)) {
            $selectedVal = (string) $selectedOption;
            foreach ($options as $val => $text) {
                $val = (string) $val;
                $html .= '<option value="' . $this->e(
                        $val
                    ) . '"' . ($val === $selectedVal ? ' selected' : '') . '>' . $this->e($text) . '</option>';
            }
            return $html;
        }
        if (!is_array($selectedOption) && !$selectedOption instanceof Traversable) {
            throw new UnexpectedValueException();
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
            $html .= '<option value="' . $this->e(
                    $value
                ) . '"' . (isset($selectedOptions[$value]) ? ' selected' : '') . '>' . $this->e($text) . '</option>';
        }
        return $this->formEl($html);
    }

    public function httpMethodField(string $method = null, array $attribs = null): string {
        return $this->formEl($this->hiddenField(['name' => '_method', 'value' => $method] + (array) $attribs));
    }

    public function openTag(string $tagName, array $attribs = [], bool $isXml = false): string {
        return '<'
            . $this->e($tagName)
            . $this->attribs($attribs)
            . ($isXml ? ' />' : '>');
    }

    public function closeTag(string $name): string {
        return '</' . $this->e($name) . '>';
    }

    public function tag1(string $tagName, array $attribs = null, array $conf = []): string {
        $conf['single'] = true;
        return $this->tag($tagName, null, $attribs, $conf);
    }

    public function tag(string $tagName, string $text = null, array $attribs = null, array $conf = null): string {
        $conf = Conf::check(
            [
                'escape' => true,
                'single' => false,
                'xml'    => false,
                'eol'    => false,
            ],
            (array) $conf
        );
        $output = $this->openTag($tagName, (array) $attribs, $conf['xml']);
        if (!$conf['single']) {
            $output .= $conf['escape'] ? $this->e($text) : $text;
            $output .= $this->closeTag($tagName);
        }
        if ($conf['eol']) {
            $output .= "\n";
        }
        return $output;
    }

    /**
     * The source was found in Drupal-7.
     */
    public function attribs(array $attribs): string {
        foreach ($attribs as $attrib => &$data) {
            if (!is_numeric($attrib)) {
                $data = implode(' ', (array) $data);
                $data = $attrib . '="' . $this->e($data) . '"';
            }
        }
        unset($data);
        return $attribs ? ' ' . implode(' ', $attribs) : '';
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
    public function uriWithRedirectToSelf(string|Uri $uri): string {
        $newUri = $this->request->prependUriWithBasePath(is_string($uri) ? $uri : $uri->toStr(null, false));
        $newUri->query()['redirect'] = $this->uri()->toStr(null, false);
        return $newUri->toStr(null, true);
    }

    /**
     * Renders link - HTML `a` tag.
     */
    public function link(string|Uri $uri, string $text, array $attribs = null, array $conf = null): string {
        $attribs = (array) $attribs;
        $attribs['href'] = $this->request->prependUriWithBasePath(
            is_string($uri) ? $uri : $uri->toStr(null, false)
        )->toStr(null, false);
        return $this->tag('a', $text, $attribs, $conf);
    }

    public function copyright(string $brand, string|int $startYear = null): string {
        $currentYear = date('Y');
        if ($startYear === $currentYear) {
            $range = $currentYear;
        } else {
            $range = intval($startYear) . '-' . $currentYear;
        }
        return '© ' . $range . ', ' . $this->e($brand);
    }

    public function jsConf(): ArrayObject {
        if (!isset($this->request['jsConf'])) {
            $this->request['jsConf'] = new ArrayObject();
        }
        return $this->request['jsConf'];
    }

    public function toJson(mixed $val): string {
        return toJson($val);
    }

    public static function e($text): string {
        return htmlspecialchars((string) $text, ENT_QUOTES);
    }

    /**
     * Opposite to e().
     */
    public static function de($text): string {
        return htmlspecialchars_decode((string) $text, ENT_QUOTES);
    }

    public function plugin(string $name): mixed {
        $name = ucfirst($name);
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = ($this->pluginFactory)($name);
        }
        return $this->plugins[$name];
    }

    public function __call(string $pluginName, array $args) {
        $plugin = $this->plugin($pluginName);
        return $plugin($args);
    }

    /*public function handlerInstance() {
        return $this->request->handler()['instance'];
    }*/

    protected function sourceAbsFilePath(string $sourceAbsOrRelFilePath, bool $throwExIfNotFound = true): bool|string {
        $sourceAbsOrRelFilePath .= self::VIEW_FILE_EXT;
        if (Path::isAbs($sourceAbsOrRelFilePath) && is_readable($sourceAbsOrRelFilePath)) {
            return $sourceAbsOrRelFilePath;
        }
        for ($i = count($this->baseSourceDirPaths()) - 1; $i >= 0; $i--) {
            $baseSourceDirPath = $this->baseSourceDirPaths[$i];
            $sourceAbsFilePath = Path::combine($baseSourceDirPath, $sourceAbsOrRelFilePath);
            if (is_readable($sourceAbsFilePath)) {
                return $sourceAbsFilePath;
            }
        }
        if ($throwExIfNotFound) {
            throw new RuntimeException(
                "Unable to detect an absolute file path for the path '$sourceAbsOrRelFilePath', searched in paths:\n'"
                . implode(PATH_SEPARATOR, $this->baseSourceDirPaths) . "'"
            );
        }
        return false;
    }

    /**
     * Can be used to wrap around any form field extra HTML.
     */
    protected function formEl(string $html): string {
        return $html;
    }

    private function init(): void {
        self::$htmlIds = [];
    }

    private function addCommonAttribs(array $attribs): array {
        if (!isset($attribs['id']) && isset($attribs['name'])) {
            $attribs['id'] = $this->htmlId($attribs['name']);
        }
        return $attribs;
    }
}
