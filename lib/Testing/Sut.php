<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Base\TSingleton;
use function Morpho\App\moduleDirPath;
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

    protected function uriAuthority(): string {
        if (!isset($this->uriAuthority)) {
            $uriAuthority = \getenv('MORPHO_TEST_URI_AUTHORITY');
            if (false === $uriAuthority) {
                $uriAuthority = $this->isCi() ? '127.0.0.1' : 'framework';
            }
            $this->uriAuthority = $uriAuthority;
        }
        return $this->uriAuthority;
    }

    public function uri(): string {
        return 'http://' . $this->uriAuthority();
    }

    public function seleniumDirPath(): string {
        return $this->baseDirPath() . '/' . TEST_DIR_NAME . '/Integration';
    }
}
