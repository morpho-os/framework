<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Base\TSingleton;
use function Morpho\App\moduleDirPath;
//use const Morpho\App\MODULE_DIR_NAME;
use const Morpho\App\Web\PUBLIC_DIR_NAME;

// SUT/System Under Test
class Sut implements ISut {
    use TSingleton;

    /**
     * @var ?string
     */
    //private $siteModuleDirPath;
    /**
     * @var ?string
     */
    private $baseDirPath;

    /**
     * @var ?string
     */
    private $publicDirPath;

    /**
     * @var ?TestConfig
     */
    private $config;

    public function baseDirPath(): string {
        if (null === $this->baseDirPath) {
            $this->baseDirPath = moduleDirPath(__DIR__);
        }
        return $this->baseDirPath;
    }

/*    public function siteModuleDirPath(): string {
        if (null === $this->siteModuleDirPath) {
            $this->siteModuleDirPath = $this->baseDirPath() . '/' . MODULE_DIR_NAME;
        }
        return $this->siteModuleDirPath;
    }*/

/*    public function configFilePath(): string {
        return $this->siteModuleDirPath() . '/config.php';
    }*/

    public function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->baseDirPath() . '/' . PUBLIC_DIR_NAME;
        }
        return $this->publicDirPath;
    }

    public function config(): \ArrayAccess {
        if (null === $this->config) {
            $this->config = $this->mkConfig();
        }
        return $this->config;
    }

    protected function mkConfig(): \ArrayAccess {
        return new SutConfig();
    }
}
