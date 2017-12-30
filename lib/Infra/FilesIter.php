<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

use Morpho\Fs\Directory;
use const Morpho\Core\{MODULE_DIR_NAME, TEST_DIR_NAME, LIB_DIR_NAME};
use const Morpho\Web\PUBLIC_DIR_NAME;
use function Morpho\Base\chain;

class FilesIter implements \IteratorAggregate {
    private $baseDirPath;

    public function __construct(string $baseDirPath) {
        $this->baseDirPath = $baseDirPath;
    }

    public function getIterator() {
        $baseDirPath = realpath($this->baseDirPath);
        // @TODO: Process modules
        yield from chain(
            Directory::filePaths($baseDirPath . '/' . LIB_DIR_NAME, null, ['recursive' => true]),
            $this->filesInTestDir($baseDirPath),
            Directory::filePaths($baseDirPath . '/' . PUBLIC_DIR_NAME . '/' . MODULE_DIR_NAME, '~\.(ts|styl)$~', ['recursive' => true]),
            $this->filesInTestDir($baseDirPath)
        );
    }

    private function filesInTestDir(string $baseDirPath): iterable {
        foreach (Directory::filePaths($baseDirPath . '/' . TEST_DIR_NAME, '~\.php$~s', ['recursive' => true]) as $filePath) {
            if (preg_match('~/' . preg_quote(TEST_DIR_NAME, '~') . '/.*?/_files/~s', $filePath)) {
                continue;
            }
            yield $filePath;
        }
        //yield from Directory::filePaths($baseDirPath . '/' . TEST_DIR_NAME . '/visual', Directory::PHP_FILES_RE);
        yield $baseDirPath . '/' . TEST_DIR_NAME . '/bootstrap.php';
    }
}