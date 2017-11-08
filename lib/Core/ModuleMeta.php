<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

class ModuleMeta extends \ArrayObject {
    protected $moduleName;

    public function __construct(string $moduleName, $meta) {
        $this->moduleName = $moduleName;
        parent::__construct($meta);
    }

    public function autoloadFilePath(): string {
        return $this->vendorDirPath() . '/' . AUTOLOAD_FILE_NAME;
    }

    public function vendorDirPath(): string {
        return $this->dirPath() . '/' . VENDOR_DIR_NAME;
    }

    public function dirPath(): string {
        return $this['paths']['dirPath'];
    }

    public function rcDirPath(): string {
        if (isset($this['paths']['rcDirPath'])) {
            return $this['paths']['rcDirPath'];
        }
        return $this['paths']['rcDirPath'] = $this->dirPath() . '/' . RC_DIR_NAME;
    }
}