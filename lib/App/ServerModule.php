<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Fs\Dir;
use const PHP_SAPI;

class ServerModule extends Module {
    private ClientModule $clientModule;

    public function autoloadFilePath(): string {
        return $this->vendorDirPath() . '/' . AUTOLOAD_FILE_NAME;
    }

    public function vendorDirPath(): string {
        return $this->dirPath() . '/' . VENDOR_DIR_NAME;
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

    public function confDirPath(): string {
        if (isset($this['path']['confDirPath'])) {
            return $this['path']['confDirPath'];
        }
        return $this['path']['confDirPath'] = $this->dirPath() . '/' . CONF_DIR_NAME;
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

    /**
     * @param bool $differentiateSapi Either return only SAPI specific paths or not.
     * @return iterable Iterable over file paths of controllers.
     */
    public function controllerFilePaths(bool $differentiateSapi): iterable {
        $paths = $this['path'];
        if (isset($paths['controllerDirPath'])) {
            $controllerDirPaths = (array)$paths['controllerDirPath'];
        } else {
            $libDirPath = $this->libDirPath();
            if ($differentiateSapi) {
                $controllerDirPaths = PHP_SAPI === 'cli' ? [$libDirPath . '/App/Cli'] : [$libDirPath . '/App/Web'];
            } else {
                $controllerDirPaths = [$libDirPath . '/App/Web', $libDirPath . '/App/Cli'];
            }
        }
        foreach ($controllerDirPaths as $controllerDirPath) {
            if (!\is_dir($controllerDirPath)) {
                continue;
            }
            yield from Dir::filePaths($controllerDirPath, '~\w' . CONTROLLER_SUFFIX . '\.php$~', true);
        }
    }

    public function setClientModule(ClientModule $module) {
        $this->clientModule = $module;
    }

    public function clientModule(): ClientModule {
        if (!isset($this->clientModule)) {
            $this->clientModule = new ClientModule($this->name, ['path' => ['dirPath' => $this['path']['clientModuleDirPath']]]);
        }
        return $this->clientModule;
    }
}
