<?php
/**
 * Slightly changed ClassLoader from the Composer project:
 * https://github.com/composer/composer/blob/master/src/Composer/Autoload/ClassLoader.php
 * Copyright (c) 2015 Nils Adermann, Jordi Boggiano
 */

declare(strict_types=1);

namespace Morpho\Base;

class ClassLoader extends Autoloader {
    // PSR-4
    private $prefixLengthsPsr4 = [];
    private $prefixDirsPsr4 = [];
    private $fallbackDirsPsr4 = [];

    // PSR-0
    private $prefixesPsr0 = [];
    private $fallbackDirsPsr0 = [];

    private $useIncludePath = false;

    private $classMap = [];
    private $classMapAuthoritative = false;

    public function getPrefixes(): array {
        return !empty($this->prefixesPsr0)
            ? call_user_func_array('array_merge', $this->prefixesPsr0)
            : [];
    }

    public function getPrefixesPsr4(): array {
        return $this->prefixDirsPsr4;
    }

    public function getFallbackDirs(): array {
        return $this->fallbackDirsPsr0;
    }

    public function getFallbackDirsPsr4(): array {
        return $this->fallbackDirsPsr4;
    }

    public function getClassToFilePathMap(): array {
        return $this->classMap;
    }

    public function addClassToFilePathMap(array $classToFilePathMap): self {
        if ($this->classMap) {
            $this->classMap = array_merge($this->classMap, $classToFilePathMap);
        } else {
            $this->classMap = $classToFilePathMap;
        }
        return $this;
    }

    public function addPrefixToFilePathMapping(string $prefix, $paths, bool $prepend = false): self {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr0 = array_merge((array) $paths, $this->fallbackDirsPsr0);
            } else {
                $this->fallbackDirsPsr0 = array_merge($this->fallbackDirsPsr0, (array) $paths);
            }
            return $this;
        }

        $first = $prefix[0];
        if (!isset($this->prefixesPsr0[$first][$prefix])) {
            $this->prefixesPsr0[$first][$prefix] = (array) $paths;

            return $this;
        }
        if ($prepend) {
            $this->prefixesPsr0[$first][$prefix] = array_merge((array) $paths, $this->prefixesPsr0[$first][$prefix]);
        } else {
            $this->prefixesPsr0[$first][$prefix] = array_merge($this->prefixesPsr0[$first][$prefix], (array) $paths);
        }

        return $this;
    }

    public function addPsr4PrefixToFilePathMapping(string $prefix, $paths, $prepend = false): self {
        if (!$prefix) {
            // Register directories for the root namespace.
            if ($prepend) {
                $this->fallbackDirsPsr4 = array_merge((array) $paths, $this->fallbackDirsPsr4);
            } else {
                $this->fallbackDirsPsr4 = array_merge($this->fallbackDirsPsr4, (array) $paths);
            }
        } elseif (!isset($this->prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        } elseif ($prepend) {
            // Prepend directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge((array) $paths, $this->prefixDirsPsr4[$prefix]);
        } else {
            // Append directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge($this->prefixDirsPsr4[$prefix], (array) $paths);
        }
        return $this;
    }

    public function setPrefixToFilePathMapping(string $prefix, $paths): self {
        if (!$prefix) {
            $this->fallbackDirsPsr0 = (array)$paths;
        } else {
            $this->prefixesPsr0[$prefix[0]][$prefix] = (array)$paths;
        }
        return $this;
    }

    public function setPsr4PrefixToFilePathMapping(string $prefix, $paths): self {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array)$paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array)$paths;
        }
        return $this;
    }

    /**
     * Should the include be used during class search?
     *
     * @param $useIncludePath bool|null
     */
    public function useIncludePath($useIncludePath = null): bool {
        if (null !== $useIncludePath) {
            $this->useIncludePath = $useIncludePath;
        }
        return $this->useIncludePath;
    }

    /**
     * Should class lookup fail if not found in the current class map?
     *
     * @param $classMapAuthoritative bool|null
     */
    public function isClassMapAuthoritative($classMapAuthoritative = null): bool {
        if (null !== $classMapAuthoritative) {
            $this->classMapAuthoritative = $classMapAuthoritative;
        }
        return $this->classMapAuthoritative;
    }

    public function findFilePath(string $class) {
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        // class map lookup
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }
        if ($this->classMapAuthoritative) {
            return false;
        }

        $file = $this->findFileWithExtension($class, '.php');

        // Search for Hack files if we are running on HHVM
        if ($file === null && defined('HHVM_VERSION')) {
            $file = $this->findFileWithExtension($class, '.hh');
        }

        if ($file === null) {
            // Remember that this class does not exist.
            return $this->classMap[$class] = false;
        }

        return $file;
    }

    private function findFileWithExtension($class, $ext) {
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            foreach ($this->prefixLengthsPsr4[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($this->prefixDirsPsr4[$prefix] as $dir) {
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-4 fallback dirs
        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
                return $file;
            }
        }

        // PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR) . $ext;
        }

        if (isset($this->prefixesPsr0[$first])) {
            foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-0 fallback dirs
        foreach ($this->fallbackDirsPsr0 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                return $file;
            }
        }

        // PSR-0 include paths.
        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
            return $file;
        }
    }
}
