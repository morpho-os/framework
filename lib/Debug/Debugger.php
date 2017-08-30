<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Debug;

use Morpho\Base\NotImplementedException;
use function Morpho\Base\{typeOf, buffer};

/**
 * Utility class to debug any PHP application.
 * To debug applications, consider to add/change the following php.ini settings:
 *     html_errors = 0
 *     ; if you use xdebug:
 *     xdebug.var_display_max_data=-1
 *     xdebug.var_display_max_depth=-1
 */
class Debugger {
    protected $ignoredFrames = [];

    protected $isHtmlMode;

    private static $instance;

    private static $class;
    
    private $exitCode = 0;

    public function type($obj) {
        $this->dump(typeOf($obj));
    }
    
    public function dump() {
        $argsCount = func_num_args();
        $output = '';
        for ($i = 0; $i < $argsCount; $i++) {
            $var = func_get_arg($i);
            $output .= $this->varToString($var);
        }
        $output .= $this->calledAt();
        if ($this->isHtmlMode()) {
            $output = $this->formatHtml($output);
        }
        echo $output;
        exit($this->exitCode);
    }

    public function trace() {
        $output = $this->traceToString();
        if ($this->isHtmlMode()) {
            $output = $this->formatHtml($output);
        }
        echo $output;
        exit($this->exitCode);
    }

    /**
     * @param string|object $object An object for that methods will be dumped.
     * @param int $filter Logical combination of the following constants:
     *                              ReflectionMethod::IS_STATIC
     *                              ReflectionMethod::IS_PUBLIC
     *                              ReflectionMethod::IS_PROTECTED
     *                              ReflectionMethod::IS_protected
     *                              ReflectionMethod::IS_ABSTRACT
     *                              ReflectionMethod::IS_FINAL
     * @param string|null $regexp
     * @param bool $sort Either sort result or not
     */
    public function methods($object, $filter = null, $regexp = null, $sort = false) {
        if (null === $filter) {
            $filter = \ReflectionMethod::IS_PUBLIC;
        }
        $r = is_object($object) ? new \ReflectionObject($object) : new \ReflectionClass($object);
        $methods = [];
        foreach ($r->getMethods($filter) as $method) {
            if (null !== $regexp) {
                if (!preg_match($regexp, $method->getName())) {
                    continue;
                }
            }
            // @TODO: Add arguments list.
            $methods[] = $method->getName() . '()';
        }
        if ($sort) {
            sort($methods);
        }
        $this->dump($methods);
    }

    public function varDump() {
        $argsCount = func_num_args();
        $output = '';
        for ($i = 0; $i < $argsCount; $i++) {
            $var = func_get_arg($i);
            $output .= $this->varToString($var);
        }
        $output .= $this->calledAt();
        if ($this->isHtmlMode()) {
            $output = $this->formatHtml($output, false);
        }
        echo $output;
    }

    /**
     * Improved version of the var_export
     */
    public function varExport($var, bool $return = false, bool $stripNumericKeys = true) {
        $php = preg_replace(
                [
                    '~=>\s+array~si',
                    '~array \(~si',
                ],
                [
                    '=> array',
                    'array(',
                ],
                var_export($var, true)
            ) . ';';
        if ($stripNumericKeys) {
            $php = preg_replace('~^(\s+)\d+.*=> ~mi', '\\1', $php);
        }
        // Reindent code: replace 2 spaces -> 4 spaces.
        $php = preg_replace_callback(
            '~^\s+~m',
            function ($match) {
                $count = substr_count($match[0], '  ');
                return str_repeat('  ', $count * 2);
            },
            $php
        );

        $output = $this->formatLine($php)
            . $this->calledAt();

        if ($this->isHtmlMode()) {
            $output = $this->formatHtml($output);
        }

        if ($return) {
            return $output;
        }
        echo $output;
    }

    public function logToFile(string $filePath, ...$args) {
        $oldHtmlMode = $this->isHtmlMode;
        $this->isHtmlMode(false);
        $content = buffer(function () use ($args) {
            $this->varDump(...$args);
        });
        if (@filesize($filePath) == 0) {
            $content = ltrim($content);
        }
        $result = @file_put_contents($filePath, $content, FILE_APPEND);
        $this->isHtmlMode = $oldHtmlMode;
        return $result !== false;
    }

    public function varToString($var, bool $fixOutput = true): string {
        $output = trim(buffer(function () use ($var) {
            if ($var instanceof \Generator) {
                var_dump("\\Generator which yields the values: " . var_export(iterator_to_array($var, false), true));
            } else {
                var_dump($var);
            }
        }));
        if ($fixOutput) {
            $output = preg_replace('~]=>\\n\s*~si', '] => ', $output);
        }
        return $this->formatLine($output);
    }

    public function traceToString(): string {
        return $this->formatLine(new Trace());
    }

    public function calledAt(): string {
        $frame = $this->findCallerFrame();
        return $this->formatLine("Debugger called at [{$frame['filePath']}:{$frame['line']}]");
    }

    public function on(callable $errorHandlerCallback = null, int $errorLevel = null) {
        throw new NotImplementedException();
        /*
        $this->oldDisplayErrors = ini_set('display_errors', 1);

        if (null === $errorHandlerCallback) {
            $errorHandlerCallback = array($this, 'errorHandler');
        }
        if (!is_callable($errorHandlerCallback)) {
            throw new \InvalidArgumentException('Invalid callback was provided.');
        }
        if (null === $errorLevel) {
            $errorLevel = E_ALL | E_STRICT;
        }
        $oldErrorHandler = set_error_handler($errorHandlerCallback, $errorLevel);
        if (null !== $oldErrorHandler) {
            array_push($this->oldErrorHandlers, $oldErrorHandler);
        }

        array_push($this->oldErrorLevels, error_reporting($errorLevel));
        */
    }

    public function off() {
        throw new NotImplementedException();
        /*
        if (null !== $this->oldDisplayErrors) {
            ini_set('display_errors', )
        }
        if (count($this->oldErrorLevels) > 0) {
            error_reporting(array_pop($this->oldErrorLevels));
        }

        if (count($this->oldErrorHandlers) > 0) {
            set_error_handler(array_pop($this->oldErrorHandlers));
        }
        */
    }

    public function isHtmlMode(bool $flag = null): bool {
        if (null !== $flag) {
            $this->isHtmlMode = $flag;
        } elseif (null === $this->isHtmlMode) {
            if (PHP_SAPI == 'cli') {
                $this->isHtmlMode = false;
            } else {
                $acceptsHtml = isset($_SERVER['HTTP_ACCEPT'])
                    && false !== stristr($_SERVER['HTTP_ACCEPT'], 'text/html');
                $this->isHtmlMode = $acceptsHtml && !$this->isPhpFormatEnabled();
            }
        }
        return $this->isHtmlMode;
    }

    public function ignoreCaller(string $filePath, int $lineNumber = null): self {
        $this->ignoredFrames[] = ['filePath' => $filePath, 'line' => $lineNumber];

        return $this;
    }

    final public static function instance() {
        if (null === self::$instance) {
            self::$instance = self::$class ? new self::$class : new self();
        }

        return self::$instance;
    }

    final public static function resetState() {
        self::$instance = null;
    }

    public static function setClass($class) {
        self::$class = $class;
    }
    
    public function setExitCode(int $exitCode): self {
        $this->exitCode = $exitCode;
        return $this;
    }

    protected function __construct() {
    }

    protected function __wakeup() {
    }

    protected function __clone() {
    }

    protected function findCallerFrame() {
        // @TODO: Move isIgnoredFrame to Trace::ignoreFrame(), then call Trace::toArray()
        $trace = (new Trace())->toArray();
        do {
            $frame = array_shift($trace);
        } while ($frame && (!isset($frame['line']) || $this->isIgnoredFrame($frame)));

        return $frame;
    }

    /**
     * @TODO: Move to Trace.
     *
     * @param Frame $frame
     * @return bool
     */
    protected function isIgnoredFrame(Frame $frame) {
        foreach ($this->ignoredFrames as $frameToIgnore) {
            if ($frame['filePath'] == $frameToIgnore['filePath']
                && (is_null($frameToIgnore['line']) || $frame['line'] == $frameToIgnore['line'])
            ) {
                return true;
            }
        }

        return false;
    }

    protected function formatLines(array $lines) {
        $output = '';
        foreach ($lines as $line) {
            $output .= $this->formatLine($line);
        }

        return $output;
    }

    protected function formatLine($line) {
        if (!$this->isHtmlMode()) {
            return "\n$line\n";
        }
        return '<pre>' . htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE) . '</pre>';
    }

    protected function formatHtml($output, $wrapOutput = true) {
        $output = str_replace(
            '<pre>',
            '<pre style="margin: 1.5em; padding: 0; font-weight: bold; color: #333;">',
            $output
        );
        if ($wrapOutput) {
            $html = <<<'OUT'
<script>
window.onload = function () {
    var bodyNode = document.getElementsByTagName('body')[0];
    var nodes = bodyNode.getElementsByTagName('*');
    var debuggerNode;
    for (var i = nodes.length; i--;) {
        var node = nodes.item(i);
        if (node.hasAttribute('id') && node.getAttribute('id') == 'morpho-debugger') {
            debuggerNode = node.cloneNode(true);
        }
    }
    bodyNode.innerHTML = '';
    bodyNode.appendChild(debuggerNode);
};
</script>
<div id="morpho-debugger" style="position: absolute; top: 0; left: 0; right: 0; background: #fff; color: #000; text-align: left;">
    <div style="border: solid 2px #3CB371; border-radius: 5px; margin: 1em; overflow: auto;">
        <h1 style="margin: 0; padding: .2em; background: #3CB371; color: #fff; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;">Debugger</h1>
        {{output}}
    </div>
</div>
OUT;
            return str_replace(['{{output}}'], $output, $html);
        }

        return $output;
    }

    protected function isPhpFormatEnabled(): bool {
        return ini_get('html_errors')
        && ini_get('xdebug.default_enable')
        && ini_get('xdebug.overload_var_dump');
    }

    protected function errorHandler($level, $message, $filePath, $line, $context) {
        if ($level & error_reporting()) {
            try {
                // @TODO: Sync with PHP 7.
                $types = [
                    E_ERROR             => 'Error',
                    E_WARNING           => 'Warning',
                    E_PARSE             => 'Parse error',
                    E_NOTICE            => 'Notice',
                    E_CORE_ERROR        => 'Core error',
                    E_CORE_WARNING      => 'Core warning',
                    E_COMPILE_ERROR     => 'Compile error',
                    E_COMPILE_WARNING   => 'Compile warning',
                    E_USER_ERROR        => 'User error',
                    E_USER_WARNING      => 'User warning',
                    E_USER_NOTICE       => 'User notice',
                    E_STRICT            => 'Strict warning',
                    E_RECOVERABLE_ERROR => 'Recoverable fatal error',
                    E_DEPRECATED        => 'Deprecated notice',
                ];
                $message = $types[$level] . ': ' . $message;

                // Hack to get informative backtrace.
                throw new \ErrorException($message, 0, $level, $filePath, $line);
            } catch (\Exception $e) {
                $this->dump($e->__toString());
            }
        }
    }
}
