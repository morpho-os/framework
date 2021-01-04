<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use ArrayObject;
use Morpho\Base\Pipe;
use Morpho\Fs\File;
use RuntimeException;
use Throwable;
use function array_merge;
use function extract;
use function get_class;
use function is_array;
use function is_string;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function trim;

class TemplateEngine extends Pipe {
    protected array $vars = [];
    protected string $targetDirPath;
    private bool $forceCompile = false;

    public function setTargetDirPath(string $targetDirPath): void {
        $this->targetDirPath = $targetDirPath;
    }

    public function forceCompile(bool $flag = null): bool {
        if (null !== $flag) {
            return $this->forceCompile = $flag;
        }
        return $this->forceCompile;
    }

    public function tpl(string $__filePath, array $__vars): string {
        // NB: We can't use the Base\tpl() function as we need to preserve $this
        extract($__vars, EXTR_SKIP);
        unset($__vars);
        ob_start();
        try {
            require $__filePath;
        } catch (Throwable $e) {
            // Don't output any result in case of Error
            ob_end_clean();
            throw $e;
        }
        return trim(ob_get_clean());
    }

    /**
     * @param string|array|ArrayObject $context
     * @param array $vars
     */
    public function run($context, array $__vars): string {
        if (is_array($context)) {
            $context = new ArrayObject($context);
        } elseif (is_string($context)) {
            $context = new ArrayObject(['code' => $context]);
        }
        //$context['vars'] = $__vars;
        // 1. Compile
        $context = $this->__invoke($context);
        // 2. tpl() without file inclusion.
        $__code = $context['code'];
        //$__vars = $context['vars'];
        unset($context);
        extract($__vars, EXTR_SKIP);
        ob_start();
        try {
            eval('?>' . $__code);
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return trim(ob_get_clean());
    }

    /**
     * Compiles and renders the $filePath.
     * @param array|ArrayObject $vars
     */
    public function runFile(string $filePath, $vars = []): string {
        $filePath = $this->compileFile($filePath);
        return $this->tpl($filePath, is_array($vars) ? $vars : $vars->getArrayCopy());
    }

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

    /**
     * @param string $sourceFilePath
     * @return string Path to the compiled file.
     */
    protected function compileFile(string $sourceFilePath): string {
        if (!$this->targetDirPath) {
            throw new RuntimeException("The property '" . get_class($this) . "::targetDirPath' is empty");
        }
        $targetFilePath = $this->targetDirPath . '/' . md5($sourceFilePath) . '.php';
        if (!is_file($targetFilePath) || $this->forceCompile) {
            $code = File::read($sourceFilePath);
            $context = new ArrayObject([
                'code' => $code,
                //'vars' => [],
                'filePath' => $sourceFilePath,
                'conf' => [
                    'appendSourceInfo' => true, // @TODO: Pass compiler conf
                ],
            ]);
            $context = $this->__invoke($context);
            File::write($targetFilePath, $context['code'], ['lock' => true]);
        }
        return $targetFilePath;
    }
}
