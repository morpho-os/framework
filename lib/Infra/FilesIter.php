<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

use function Morpho\Base\filter;
use Morpho\Fs\Dir;
use const Morpho\Core\{
    MODULE_DIR_NAME, TEST_DIR_NAME, LIB_DIR_NAME, VIEW_DIR_NAME
};
use const Morpho\Web\PUBLIC_DIR_NAME;
use function Morpho\Base\chain;

class FilesIter implements \IteratorAggregate {
    private $baseDirPath;

    public function __construct(string $baseDirPath) {
        $this->baseDirPath = $baseDirPath;
    }

    public function getIterator() {
        $baseDirPath = realpath($this->baseDirPath);
        yield from chain(
            $this->filePaths($baseDirPath . '/' . LIB_DIR_NAME),
            filter($this->filterTestsFn(), $this->phpFilePaths($baseDirPath . '/' . TEST_DIR_NAME)),
            $this->filePathsInModuleDir($baseDirPath),
            $this->filePathsInPublicDir($baseDirPath . '/' . PUBLIC_DIR_NAME . '/' . MODULE_DIR_NAME)
        );
    }

    private function filePathsInModuleDir(string $baseDirPath): iterable {
        foreach (Dir::dirPaths($baseDirPath . '/' . MODULE_DIR_NAME) as $dirPath) {
            if (is_dir($dirPath . '/' . LIB_DIR_NAME)) {
                yield from $this->filePaths($dirPath . '/' . LIB_DIR_NAME);
            }
            if (is_dir($dirPath . '/' . VIEW_DIR_NAME)) {
                yield from $this->filePaths($dirPath . '/' . VIEW_DIR_NAME);
            }
            if (is_dir($dirPath . '/' . TEST_DIR_NAME)) {
                yield from filter($this->filterTestsFn(), $this->phpFilePaths($dirPath . '/' . TEST_DIR_NAME));
            }
            if (is_dir($dirPath . '/' . PUBLIC_DIR_NAME)) {
                yield from $this->filePathsInPublicDir($dirPath . '/' . PUBLIC_DIR_NAME);
            }
        }
    }

    private function filterTestsFn(): \Closure {
        return function ($filePath) {
            return !preg_match('~/' . preg_quote(TEST_DIR_NAME, '~') . '/.*?/_files/~s', $filePath);
        };
    }

    private function phpFilePaths(string $dirPath): iterable {
        return $this->filePaths($dirPath, '~\.php$~s');
    }

    private function filePathsInPublicDir(string $dirPath): iterable {
        return $this->filePaths($dirPath, '~\.(ts|styl|js)$~');
    }

    private function filePaths(string $dirPath, $filter = null): iterable {
        return Dir::filePaths($dirPath, $filter, ['recursive' => true]);
    }
}