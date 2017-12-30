<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\EmptyPropertyException;
use Morpho\Base\ItemNotSetException;
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

    public function renderFileWithoutCompilation($__filePath, array $__vars): string {
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
     * Runs Pipe handlers.
     */
    public function render(string $code, array $vars = []): string {
        $context = $this->__invoke(new \ArrayObject(['code' => $code, 'vars' => $vars]));
        $__code = $context['code'];
        $__vars = $context['vars'];
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
    public function renderFile(string $filePath, $vars = []): string {
        $filePath = $this->compileFile($filePath);
        return $this->renderFileWithoutCompilation($filePath, is_array($vars) ? $vars : $vars->getArrayCopy());
    }

    public function useCache(bool $flag = null): bool {
        if (null !== $flag) {
            $this->useCache = $flag;
        }
        return $this->useCache;
    }

    public function __set(string $varName, $value): void {
        $this->vars[$varName] = $value;
    }

    public function __get(string $varName) {
        if (!isset($this->vars[$varName])) {
            throw new ItemNotSetException("The template variable '$varName' was not set.");
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

    protected function compileFile(string $filePath): string {
        if (!$this->cacheDirPath) {
            throw new EmptyPropertyException($this, 'cacheFilePath');
        }
        $this->uniqueFileHash = md5($this->uniqueFileHash . '|' . $filePath);
        $cacheFilePath = $this->cacheDirPath . '/' . $this->uniqueFileHash . '.php';
        if (!is_file($cacheFilePath) || !$this->useCache()) {
            // @TODO: Replace the setFilePath with $context
            foreach ($this as $fn) {
                $fn->setFilePath($filePath);
            }
            $php = $this->__invoke(File::read($filePath));
            $res = file_put_contents($cacheFilePath, $php, LOCK_EX);
            if (false === $res) {
                throw new \RuntimeException("Unable to write the compiled file '$cacheFilePath'");
            }
        }
        return $cacheFilePath;
    }
}