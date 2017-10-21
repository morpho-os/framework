<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use const Morpho\Core\BIN_DIR_NAME;
use const Morpho\Core\CACHE_DIR_NAME;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Core\LOG_DIR_NAME;
use Morpho\Core\ModuleMeta as BaseModuleMeta;
use const Morpho\Core\TEST_DIR_NAME;
use const Morpho\Core\TMP_DIR_NAME;

class ModuleMeta extends BaseModuleMeta {
    public function logDirPath(): string {
        if (isset($this['paths']['logDirPath'])) {
            return $this['paths']['logDirPath'];
        }
        return $this['paths']['logDirPath'] = $this->dirPath() . '/' . LOG_DIR_NAME;
    }

    public function cacheDirPath(): string {
        if (isset($this['paths']['cacheDirPath'])) {
            return $this['paths']['cacheDirPath'];
        }
        return $this['paths']['cacheDirPath'] = $this->dirPath() . '/' . CACHE_DIR_NAME;
    }

    public function viewDirPath(): string {
        if (isset($this['paths']['viewDirPath'])) {
            return $this['paths']['viewDirPath'];
        }
        return $this['paths']['viewDirPath'] = $this->dirPath() . '/' . VIEW_DIR_NAME;
    }

    public function controllerDirPath(): string {
        if (isset($this['paths']['controllerDirPath'])) {
            return $this['paths']['controllerDirPath'];
        }
        return $this['paths']['controllerDirPath'] = $this->libDirPath() . '/Web';
    }

    public function rcDirPath(): string {
        if (isset($this['paths']['rcDirPath'])) {
            return $this['paths']['rcDirPath'];
        }
        return $this['paths']['rcDirPath'] = $this->dirPath() . '/' . CACHE_DIR_NAME;
    }

    public function libDirPath(): string {
        if (isset($this['paths']['libDirPath'])) {
            return $this['paths']['libDirPath'];
        }
        return $this['paths']['libDirPath'] = $this->dirPath() . '/' . LIB_DIR_NAME;
    }

    public function publicDirPath(): string {
        return $this['paths']['publicDirPath'];
    }
    
    public function configDirPath(): string {
        if (isset($this['paths']['configDirPath'])) {
            return $this['paths']['configDirPath'];
        }
        return $this['paths']['configDirPath'] = $this->dirPath() . '/' . CONFIG_DIR_NAME;
    }
    
    public function tmpDirPath(): string {
        if (isset($this['paths']['tmpDirPath'])) {
            return $this['paths']['tmpDirPath'];
        }
        return $this['paths']['tmpDirPath'] = $this->dirPath() . '/' . TMP_DIR_NAME;
    }
    
    public function binDirPath(): string {
        if (isset($this['paths']['binDirPath'])) {
            return $this['paths']['binDirPath'];
        }
        return $this['paths']['binDirPath'] = $this->dirPath() . '/' . BIN_DIR_NAME;
    }
    
    public function testDirPath(): string {
        if (isset($this['paths']['testDirPath'])) {
            return $this['paths']['testDirPath'];
        }
        return $this['paths']['testDirPath'] = $this->dirPath() . '/' . TEST_DIR_NAME;
    }
}