<?php
namespace Morpho\Loader;

class ClassNameAutoloader extends BaseAutoloader {
    protected $namespaces = array();

    public function __construct($register = true) {
        if ($register) {
            $this->mapDefault();
        }
    }

    public function mapNsToPath($ns, $paths) {
        $ns = trim($ns, '\\');
        if (isset($this->namespaces[$ns])) {
            $this->namespaces[$ns] = array_merge(
                $this->namespaces[$ns],
                (array)$paths
            );
        } else {
            $this->namespaces[$ns] = (array)$paths;
        }

        return $this;
    }

    public function findFilePath($class) {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        foreach ($this->namespaces as $ns => $dirPaths) {
            if (0 === strpos($class, $ns . '\\')) {
                $classPath = str_replace('\\', '/', substr($class, strlen($ns) + 1)) . '.php';
                foreach ($dirPaths as $dirPath) {
                    $filePath = $dirPath . '/' . $classPath;
                    if (file_exists($filePath)) {
                        return $filePath;
                    }
                }
            }
        }
    }

    protected function mapDefault() {
        $this->mapNsToPath('Morpho', dirname(dirname(__DIR__)));
    }
}
