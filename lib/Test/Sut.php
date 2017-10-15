<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use Morpho\Base\NotImplementedException;
use const Morpho\Core\CONFIG_FILE_NAME;
use const Morpho\Core\MODULE_DIR_NAME;
use Morpho\Web\PathManager;
use const Morpho\Web\PUBLIC_DIR_NAME;

// SUT/System Under Test
class Sut {
    private static $instance;

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

    public static function instance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function baseDirPath() {
        if (null === $this->baseDirPath) {
            $this->baseDirPath = PathManager::detectBaseDirPath(__DIR__);
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
        return $this->baseModuleDirPath() . '/' . CONFIG_FILE_NAME;
    }

    public function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->baseDirPath() . '/' . PUBLIC_DIR_NAME;
        }
        return $this->publicDirPath;
    }

    public function siteConfig(array $dbConfig): array {
        throw new NotImplementedException();
/*        $config = require $this->baseModuleDirPath() . '/localhost/' . CONFIG_DIR_NAME . '/' . CONFIG_FILE_NAME;
        $config['services']['db'] = $dbConfig;
        $config['errorHandler'] = [
            'dumpListener' => false,
            'noDupsListener' => false,
        ];
        $config['modules'][SystemModule::NAME]['throwDispatchErrors'] = false;
        return $config;*/
    }

    public function settings(): TestSettings {
        if (null === $this->settings) {
            $this->settings = new TestSettings();
        }
        return $this->settings;
    }
}

class TestSettings {
    private static $values = [];

    private static $default = [
        'siteUri' => 'http://framework'
    ];

    public static function set(string $name, $value) {
        self::$values[$name] = $value;
    }

    public static function get(string $name) {
        if (!array_key_exists($name, self::$values)) {
            return self::$default[$name];
        }
        return self::$values[$name];
    }

    public static function has(string $name): bool {
        return array_key_exists($name, self::$values) || array_key_exists($name, self::$default);
    }
}