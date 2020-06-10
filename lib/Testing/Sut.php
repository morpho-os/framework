<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Base\TSingleton;
use Morpho\Network\TcpAddress;
use function Morpho\App\moduleDirPath;
use const Morpho\App\CLIENT_MODULE_DIR_NAME;
use const Morpho\App\SERVER_MODULE_DIR_NAME;
use const Morpho\App\TEST_DIR_NAME;

// SUT/System Under Test
class Sut extends \ArrayObject {
    use TSingleton;

    protected string $uriAuthority;

    protected string $baseDirPath;

    public function serverModuleDirIt(): iterable {
        foreach (glob($this->baseDirPath() . '/' . SERVER_MODULE_DIR_NAME . '/[0-9a-z]*') as $path) {
            if (is_dir($path)) {
                yield $path;
            }
        }
    }

    public function isCi(): bool {
        return !empty(\getenv('MORPHO_CI'));
    }

    public function baseDirPath(): string {
        if (!isset($this->baseDirPath)) {
            $this->baseDirPath = moduleDirPath(__DIR__);
        }
        return $this->baseDirPath;
    }

    public function webServerAddress(): TcpAddress {
        $domain = getenv('MORPHO_TEST_WEB_SERVER_DOMAIN') ?: 'framework';
        $port = getenv('MORPHO_TEST_WEB_SERVER_PORT') ?: 80;
        return new TcpAddress($domain, (int) $port);
    }

    public function webServerWebDirPath(): string {
        return $this->baseDirPath() . '/' . CLIENT_MODULE_DIR_NAME;
    }

    public function uri(): string {
        $webServerAddress = $this->webServerAddress();
        return 'http://' . $webServerAddress->host() . ':' . $webServerAddress->port();
    }

    public function seleniumDirPath(): string {
        return $this->baseDirPath() . '/' . TEST_DIR_NAME . '/Integration';
    }
}
