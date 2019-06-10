<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Base\TSingleton;
use function Morpho\App\moduleDirPath;
use const Morpho\App\MODULE_DIR_NAME;
use const Morpho\App\TEST_DIR_NAME;
use const Morpho\App\Web\PUBLIC_DIR_NAME;

// SUT/System Under Test
class Sut extends \ArrayObject {
    private $domain;

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

    use TSingleton;

    public function offsetGet($name) {
        switch ($name) {
            case 'baseModuleDirPath':
                return $this->baseModuleDirPath();
            case 'baseDirPath':
                return $this->baseDirPath();
            case 'isTravis':
                return $this->isTravis();
            case 'publicDirPath':
                return $this->publicDirPath();
            case 'domain':
                return $this->domain();
            case 'uri':
                return $this->uri();
            case 'seleniumDirPath':
                return $this->seleniumDirPath();
            default:
                throw new \UnexpectedValueException('value with key ' . $name . ' does not exist');
        }
    }

    private function isTravis(): bool {
        return !empty(\getenv('TRAVIS'));
    }

    private function baseModuleDirPath(): string {
        return $this->baseDirPath() . '/' . MODULE_DIR_NAME;
    }

    private function baseDirPath(): string {
        if (null === $this->baseDirPath) {
            $this->baseDirPath = moduleDirPath(__DIR__);
        }
        return $this->baseDirPath;
    }

    private function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->baseDirPath() . '/' . PUBLIC_DIR_NAME;
        }
        return $this->publicDirPath;
    }

    private function domain(): string {
        if (null === $this->domain) {
            $domain = \getenv('DOMAIN');
            if (false === $domain) {
                $domain = $this->isTravis() ? '127.0.0.1' : 'framework';
            }
            $this->domain = $domain;
        }
        return $this->domain;
    }

    private function uri(): string {
        return 'http://' . $this->domain();
    }

    private function seleniumDirPath(): string {
        return $this->baseDirPath() . '/' . TEST_DIR_NAME . '/Integration';
    }
}
