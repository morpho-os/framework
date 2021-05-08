<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use ArrayObject;
use Morpho\App\BackendModule;
use Morpho\Base\TSingleton;

use function getenv;

use const Morpho\App\BACKEND_DIR_NAME;
use const Morpho\App\FRONTEND_DIR_NAME;
use const Morpho\App\TEST_DIR_NAME;

// SUT/System Under Test
class Sut extends ArrayObject {
    use TSingleton;

    protected string $uriAuthority;

    protected string $baseDirPath;

    public function backendModuleIterator(): iterable {
        foreach (glob($this->baseDirPath() . '/' . BACKEND_DIR_NAME . '/[0-9a-z]*') as $path) {
            if (is_dir($path)) {
                yield $path;
            }
        }
    }

    public function isCi(): bool {
        return !empty(getenv('MORPHO_CI'));
    }

    public function baseDirPath(): string {
        if (!isset($this->baseDirPath)) {
            $this->baseDirPath = BackendModule::findModuleDir(__DIR__);
        }
        return $this->baseDirPath;
    }

    public function webServerAddress(): array {
        $domain = getenv('MORPHO_TEST_WEB_SERVER_DOMAIN') ?: 'framework';
        $port = getenv('MORPHO_TEST_WEB_SERVER_PORT') ?: 80;
        return ['host' => $domain, 'port' => (int) $port];
    }

    public function webServerWebDirPath(): string {
        return $this->baseDirPath() . '/' . FRONTEND_DIR_NAME;
    }

    public function testRcDirPath(): string {
        return getenv('MORPHO_TEST_RC_DIR_PATH') ?: $this->baseDirPath() . '/' . TEST_DIR_NAME . '/Integration';
    }

    public function siteUri(): string {
        $webServerAddress = $this->webServerAddress();
        return 'http://' . $webServerAddress['host'] . ':' . $webServerAddress['port'];
    }

    public function webDriverConf(): array {
        $geckoBinFilePath = $this->testRcDirPath() . '/geckodriver';
        $geckoBinCandidateFilePath = getenv('MORPHO_GECKO_BIN_FILE_PATH');
        if (false !== $geckoBinCandidateFilePath && file_exists($geckoBinCandidateFilePath)) {
            $geckoBinFilePath = $geckoBinCandidateFilePath;
        }
        return ['geckoBinFilePath' => $geckoBinFilePath];
    }

    public function dbConf(): array {
        return [
            'driver'   => 'mysql',
            'host'     => getenv('MORPHO_TEST_DB_HOST') ?: '127.0.0.1',
            'user'     => getenv('MORPHO_TEST_DB_USER') ?: 'root',
            'password' => getenv('MORPHO_TEST_DB_PASSWORD') ?: '',
            'db'       => getenv('MORPHO_TEST_DB_DB') ?: 'test',
        ];
    }
}
