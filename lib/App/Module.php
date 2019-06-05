<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

class Module extends \ArrayObject {
    protected $name;

    public function __construct(string $moduleName, $meta) {
        $this->name = $moduleName;
        parent::__construct($meta);
    }

    public function name(): string {
        return $this->name;
    }

    public function autoloadFilePath(): string {
        return $this->vendorDirPath() . '/' . AUTOLOAD_FILE_NAME;
    }

    public function vendorDirPath(): string {
        return $this->dirPath() . '/' . VENDOR_DIR_NAME;
    }

    public function dirPath(): string {
        return $this['path']['dirPath'];
    }

    public function rcDirPath(): string {
        if (isset($this['path']['rcDirPath'])) {
            return $this['path']['rcDirPath'];
        }
        return $this['path']['rcDirPath'] = $this->dirPath() . '/' . RC_DIR_NAME;
    }

    public function binDirPath(): string {
        if (isset($this['path']['binDirPath'])) {
            return $this['path']['binDirPath'];
        }
        return $this['path']['binDirPath'] = $this->dirPath() . '/' . BIN_DIR_NAME;
    }

    public function logDirPath(): string {
        if (isset($this['path']['logDirPath'])) {
            return $this['path']['logDirPath'];
        }
        return $this['path']['logDirPath'] = $this->dirPath() . '/' . LOG_DIR_NAME;
    }

    public function cacheDirPath(): string {
        if (isset($this['path']['cacheDirPath'])) {
            return $this['path']['cacheDirPath'];
        }
        return $this['path']['cacheDirPath'] = $this->dirPath() . '/' . CACHE_DIR_NAME;
    }

    public function viewDirPath(): string {
        if (isset($this['path']['viewDirPath'])) {
            return $this['path']['viewDirPath'];
        }
        return $this['path']['viewDirPath'] = $this->dirPath() . '/' . VIEW_DIR_NAME;
    }

    public function libDirPath(): string {
        if (isset($this['path']['libDirPath'])) {
            return $this['path']['libDirPath'];
        }
        return $this['path']['libDirPath'] = $this->dirPath() . '/' . LIB_DIR_NAME;
    }

    public function publicDirPath(): string {
        return $this['path']['publicDirPath'];
    }

    public function configDirPath(): string {
        if (isset($this['path']['configDirPath'])) {
            return $this['path']['configDirPath'];
        }
        return $this['path']['configDirPath'] = $this->dirPath() . '/' . CONFIG_DIR_NAME;
    }

    public function tmpDirPath(): string {
        if (isset($this['path']['tmpDirPath'])) {
            return $this['path']['tmpDirPath'];
        }
        return $this['path']['tmpDirPath'] = $this->dirPath() . '/' . TMP_DIR_NAME;
    }

    public function testDirPath(): string {
        if (isset($this['path']['testDirPath'])) {
            return $this['path']['testDirPath'];
        }
        return $this['path']['testDirPath'] = $this->dirPath() . '/' . TEST_DIR_NAME;
    }
}
