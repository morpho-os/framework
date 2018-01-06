<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\EmptyPropertyException;
use Morpho\Base\Pipe;
use Morpho\Fs\File;

class TemplateEngine extends Pipe {
    protected $useCache = true;
    protected $vars = [];
    protected $cacheDirPath;
    protected $uniqueFileHash = '';

    public function setCacheDirPath(string $dirPath): void {
        $this->cacheDirPath = $dirPath;
    }

    public function tpl($__filePath, array $__vars): string {
        // NB: We can't use the Base\tpl() function as we need to preserve $this
        extract($__vars, EXTR_SKIP);
        unset($__vars);
        ob_start();
        try {
            require $__filePath;
        } catch (\Throwable $e) {
            // Don't output any result in case of Error
            ob_end_clean();
            throw $e;
        }
        return trim(ob_get_clean());
    }

    /**
     * @param array|\ArrayObject
     */
    public function run($context, array $__vars): string {
        if (is_array($context)) {
            $context = new \ArrayObject($context);
        } elseif (is_string($context)) {
            $context = new \ArrayObject(['code' => $context]);
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
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return trim(ob_get_clean());
    }

    /**
     * Compiles and renders the $filePath.
     * @param array|\ArrayObject $vars
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
            throw new \RuntimeException("The template variable '$varName' was not set.");
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

    public function useCache(bool $flag = null): bool {
        if (null !== $flag) {
            $this->useCache = $flag;
        }
        return $this->useCache;
    }

    /**
     * @return string Path to the compiled file.
     */
    protected function compileFile(string $filePath): string {
        if (!$this->cacheDirPath) {
            throw new EmptyPropertyException($this, 'cacheFilePath');
        }
        $this->uniqueFileHash = md5($this->uniqueFileHash . '|' . $filePath);
        $cacheFilePath = $this->cacheDirPath . '/' . $this->uniqueFileHash . '.php';
        if (!is_file($cacheFilePath) || !$this->useCache()) {
            $code = File::read($filePath);
            $context = new \ArrayObject([
                'code' => $code,
                //'vars' => [],
                'filePath' => $filePath,
                'options' => [
                    'appendSourceInfo' => true, // @TODO: Pass compiler options
                ],
            ]);
            $context = $this->__invoke($context);
            $code = $context['code'];
            $res = file_put_contents($cacheFilePath, $code, LOCK_EX);
            if (false === $res) {
                throw new \RuntimeException("Unable to write the compiled file '$cacheFilePath'");
            }
        }
        return $cacheFilePath;
    }
}