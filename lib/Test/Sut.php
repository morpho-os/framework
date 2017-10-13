<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Core\CONFIG_FILE_NAME;
use const Morpho\Core\MODULE_DIR_NAME;
use const Morpho\Web\FALLBACK_CONFIG_FILE_NAME;
use Morpho\Web\Fs;
use const Morpho\Web\PUBLIC_DIR_NAME;
use Morpho\System\Module as SystemModule;

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

    public static function instance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function baseDirPath() {
        if (null === $this->baseDirPath) {
            $this->baseDirPath = Fs::detectBaseDirPath(__DIR__);
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
        $config = require $this->baseModuleDirPath() . '/localhost/' . CONFIG_DIR_NAME . '/' . FALLBACK_CONFIG_FILE_NAME;
        $config['services']['db'] = $dbConfig;
        $config['errorHandler'] = [
            'dumpListener' => false,
            'noDupsListener' => false,
        ];
        $config['modules'][SystemModule::NAME]['throwDispatchErrors'] = false;
        return $config;
    }
}