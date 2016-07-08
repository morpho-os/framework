<?php
namespace Morpho\Web\View;

use Morpho\Base\EmptyPropertyException;
use Morpho\Base\ItemNotSetException;
use Morpho\Fs\File;
use Zend\Filter\FilterChain;

abstract class TemplateEngine extends FilterChain {
    protected $useCache = true;

    protected $vars = [];

    protected $cacheDirPath;

    protected $uniqueFileHash = '';

    public function setCacheDirPath(string $dirPath)/*: void */ {
        $this->cacheDirPath = $dirPath;
    }

    /**
     * Renders file that contains code in PHPTemplate language and returns result after of PHP execution.
     */
    public function renderFile(string $filePath, array $vars = []): string {
        $__filePath = $this->getCompiledFilePath($filePath);
        unset($filePath);
        extract($vars, EXTR_SKIP);
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
     * Renders code in PHPTemplate language and returns result after of PHP execution.
     */
    public function render(string $phpEngineCode, array $vars = []): string {
        extract($vars, EXTR_SKIP);
        ob_start();
        try {
            eval('?>' . $this->filter($phpEngineCode));
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return trim(ob_get_clean());
    }

    public function useCache(bool $flag = null): bool {
        if (null !== $flag) {
            $this->useCache = $flag;
        }
        return $this->useCache;
    }

    public function __set(string $varName, $value)/*: void */ {
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

    public function __unset(string $name)/*: void */ {
        unset($this->vars[$name]);
    }

    public function mergeVars(array $vars)/*: void */ {
        $this->vars = array_merge($this->vars, $vars);
    }

    public function setVars(array $vars)/*: void */ {
        $this->vars = $vars;
    }
    
    public function getVars(): array {
        return $this->vars;
    }

    protected function getCompiledFilePath(string $filePath): string {
        if (!$this->cacheDirPath) {
            throw new EmptyPropertyException($this, 'cacheFilePath');
        }
        $this->uniqueFileHash = md5($this->uniqueFileHash . '|' . $filePath);
        $cacheFilePath = $this->cacheDirPath . '/' . $this->uniqueFileHash . '.php';
        if (!is_file($cacheFilePath) || !$this->useCache()) {
            foreach ($this->filters as $filter) {
                $filter->setFilePath($filePath);
            }
            $php = $this->filter(
                File::read($filePath)
            );
            file_put_contents($cacheFilePath, $php);
        }
        return $cacheFilePath;
    }
}
