<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Base\TSingleton;
use function Morpho\App\Core\moduleDirPath;
use const Morpho\App\Core\MODULE_DIR_NAME;
use const Morpho\App\Web\PUBLIC_DIR_NAME;

// SUT/System Under Test
class Sut {
    use TSingleton;

    /**
     * @var ?string
     */
    private $baseModuleDirPath;
    /**
     * @var ?string
     */
    private $baseDirPath;

    /**
     * @var ?string
     */
    private $publicDirPath;

    /**
     * @var ?TestSettings
     */
    private $settings;

    public function baseDirPath() {
        if (null === $this->baseDirPath) {
            $this->baseDirPath = moduleDirPath(__DIR__);
        }
        return $this->baseDirPath;
    }

    public function baseModuleDirPath(): string {
        if (null === $this->baseModuleDirPath) {
            $this->baseModuleDirPath = $this->baseDirPath() . '/' . MODULE_DIR_NAME;
        }
        return $this->baseModuleDirPath;
    }

    public function configFilePath(): string {
        return $this->baseModuleDirPath() . '/config.php';
    }

    public function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->baseDirPath() . '/' . PUBLIC_DIR_NAME;
        }
        return $this->publicDirPath;
    }

    public function settings(): TestSettings {
        if (null === $this->settings) {
            $this->settings = new TestSettings();
        }
        return $this->settings;
    }
}

class TestSettings implements \ArrayAccess {
    private $values = [];

    private $default = [
        'siteUri' => 'http://framework'
    ];

    public function offsetExists($name): bool {
        return array_key_exists($name, $this->values) || array_key_exists($name, $this->default);
    }

    public function offsetGet($name) {
        if (!array_key_exists($name, $this->values)) {
            return $this->default[$name];
        }
        return $this->values[$name];
    }
    public function offsetSet($name, $value) {
        $this->values[$name] = $value;
    }
    
    public function offsetUnset($name) {
        unset($this->values[$name]);
    }
}
