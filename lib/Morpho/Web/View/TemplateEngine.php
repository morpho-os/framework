<?php
namespace Morpho\Web\View;

use Morpho\Base\EmptyPropertyException;
use Morpho\Fs\File;
use Zend\Filter\FilterChain;

abstract class TemplateEngine extends FilterChain {
    protected $useCache = true;

    protected $templateVars = [];

    private $cacheDirPath;

    public function setCacheDirPath($dirPath) {
        $this->cacheDirPath = $dirPath;
    }

    public function mergeVars(array $vars) {
        $this->templateVars = array_merge($this->templateVars, $vars);
    }

    public function setVars(array $vars) {
        $this->templateVars = $vars;
    }

    /**
     * Renders file that contains code in PHPTemplate language and returns result as string.
     *
     * @param string $filePath
     * @param array $vars
     * @return string Returns result after of PHP execution.
     */
    public function renderFile($filePath, array $vars = []) {
        $__filePath = $this->getCompiledFilePath($filePath);
        unset($filePath);
        extract($vars, EXTR_SKIP);
        ob_start();
        require $__filePath;
        return trim(ob_get_clean());
    }

    /**
     * Renders code in PHPTemplate language and returns result as string.
     *
     * @param string $phpEngineCode
     * @param array $vars
     * @return string Returns result after of PHP execution.
     */
    public function render($phpEngineCode, array $vars = []) {
        extract($vars, EXTR_SKIP);
        ob_start();
        eval('?>' . $this->filter($phpEngineCode));
        return trim(ob_get_clean());
    }

    /**
     * @param bool|null $flag
     * @return bool
     */
    public function useCache($flag = null) {
        if (null !== $flag) {
            $this->useCache = $flag;
        }
        return $this->useCache;
    }

    public function __get($varName) {
        if (isset($this->templateVars[$varName])) {
            return $this->templateVars[$varName];
        }
        throw new \RuntimeException("The template variable '$varName' does not exist.");
    }

    public function __isset($varName) {
        return isset($this->templateVars[$varName]);
    }

    /**
     * @param string $filePath
     *
     * @return string Path to file containing PHP code.
     */
    protected function getCompiledFilePath($filePath) {
        if (!$this->cacheDirPath) {
            throw new EmptyPropertyException($this, 'cacheFilePath');
        }
        $cacheFilePath = $this->cacheDirPath . '/' . md5($filePath) . '.php';
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
