<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use Morpho\Web\Fs;

// SUT/System Under Test
class Sut {
    const TEST_DIR_NAME = Fs::TEST_DIR_NAME;
    const MODULE_DIR_NAME = Fs::MODULE_DIR_NAME;
    const CONFIG_FILE_NAME = Fs::CONFIG_FILE_NAME;
    const PUBLIC_DIR_NAME = Fs::PUBLIC_DIR_NAME;

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
            $this->baseModuleDirPath = $this->baseDirPath() . '/' . self::MODULE_DIR_NAME;
        }
        return $this->baseModuleDirPath;
    }

    public function configFilePath(): string {
        return $this->baseModuleDirPath() . '/' . self::CONFIG_FILE_NAME;
    }

    public function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->baseDirPath() . '/' . self::PUBLIC_DIR_NAME;
        }
        return $this->publicDirPath;
    }
}