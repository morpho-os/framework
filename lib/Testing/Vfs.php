<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Base\NotImplementedException;
use function Morpho\Base\startsWith;
use Morpho\Fs\IFs;
use Morpho\Fs\Stat;

/**
 * This implementation of VFS was written from scratch, with help of sources found in the package mikey179/vfsStream (Copyright (c) 2007-2015, Frank Kleine), which were used to find answers on some questions.
 */
class Vfs implements IFs {
    public const SCHEME = 'vfs';
    public const URI_PREFIX = self::SCHEME . '://';

    /**
     * @var VfsDir|null
     */
    private $dir;

    /**
     * @var VfsFile|null
     */
    private $file;

    /**
     * @var VfsDir|null
     */
    private static $root;

    public static function register(): void {
        stream_wrapper_register(self::SCHEME, __CLASS__);
    }

    public static function isRegistered(): bool {
        return in_array(self::SCHEME, stream_get_wrappers());
    }

    public static function unregister(): void {
        self::resetState();
        stream_wrapper_unregister(self::SCHEME);
    }

    public static function resetState(): void {
        self::$root = null;
    }

    public function __destruct() {
        $this->stream_close();
        $this->dir_closedir();
    }

    // ------------------------------------------------------------------------
    // IFs interface

    /**
     * @param string $uri
     * @param string $mode
     * @param int $flags
     * @param null|string $openedUri
     * @return bool
     */
    public function stream_open(string $uri, string $mode, int $flags, ?string &$openedUri): bool {
        if (null !== $openedUri) {
            throw new NotImplementedException();
        }
        if (0 !== $flags) {
            // @TODO
/*            if ($flags | STREAM_REPORT_ERRORS) {
                d($flags);
                trigger_error(), see http://php.net/manual/en/streamwrapper.stream-open.php
            }*/
        }
        $this->checkUri($uri);
        $parentDir = $this->parentDir($uri);
        if ($parentDir->dirExists(self::entryName($uri))) {
            throw new \RuntimeException('Unable to open file, entry is a directory');
        }
        $openMode = new VfsFileOpenMode($mode);
        if ($openMode->create()) {
            $file = $parentDir->createFile($uri, new VfsEntryStat([
                'mode' => $this->fileMode(),
            ]));
        } else {
            $file = $parentDir->file(self::entryName($uri));
        }
        $file->open($openMode);
        $this->file = $file;
        return true;
    }

    public function stream_close(): void {
        if ($this->file) {
            if ($this->file->isOpen()) {
                $this->file->close();
            }
        }
    }

    public function stream_lock(int $operation): bool {
        throw new NotImplementedException();
        //d(func_get_args());
    }

    public function stream_read(int $count): string {
        return $this->file->read($count);
    }

    public function stream_write(string $contents): int {
        return $this->file->write($contents);
    }

    public function stream_eof(): bool {
        return $this->file->eof();
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool {
        return $this->file->seek($offset, $whence);
    }

    public function stream_tell(): int {
        return $this->file->offset();
    }

    public function stream_flush(): bool {
        return true;
    }

    public function stream_truncate(int $newSize): bool {
        $this->file->truncate($newSize);
        clearstatcache(true, $this->file->uri());
        return true;
    }

    public function stream_stat(): array {
/*        if (!$this->file) {
            return [];
        }*/
        return $this->file->stat()->getArrayCopy();
    }

    /**
     * @param mixed $args
     */
    public function stream_metadata(string $uri, int $option, $args): bool {
        switch ($option) {
            case STREAM_META_TOUCH: // (The method was called in response to touch())
                if (!isset($args[0])) { // touch time/mtime
                    $args[0] = time();
                }
                if (!isset($args[1])) { // access time/atime
                    $args[1] = $args[0];
                }
                $file = $this->root()->fileByUriOrNone($uri);
                if (!$file) {
                    $this->parentDir($uri)->createFile($uri, new VfsEntryStat(['mode' => $this->fileMode()]));
                }
                break;
            case STREAM_META_OWNER_NAME: // (The method was called in response to chown() with string parameter)
                throw new NotImplementedException();
                break;
            case STREAM_META_OWNER: // (The method was called in response to chown())
                // STREAM_META_OWNER_NAME or STREAM_META_GROUP_NAME: The name of the owner user/group as string.
                throw new NotImplementedException();
                break;
            case STREAM_META_GROUP_NAME: // (The method was called in response to chgrp())
                // STREAM_META_OWNER_NAME or STREAM_META_GROUP_NAME: The name of the owner user/group as string.
                throw new NotImplementedException();
                break;
            case STREAM_META_GROUP: // (The method was called in response to chgrp())
                throw new NotImplementedException();
                break;
            case STREAM_META_ACCESS: // (The method was called in response to chmod())
                throw new NotImplementedException();
                break;
            default:
                throw new \UnexpectedValueException();
        }
        return true;
    }

    /**
     * @return resource
     */
    public function stream_cast(int $castAs) {
        throw new NotImplementedException();
        //d(func_get_args());
    }

    public function stream_set_option(int $option, int $arg1, int $arg2): bool {
        throw new NotImplementedException();
        //d(func_get_args());
    }

    public function unlink(string $uri): bool {
        $parentDir = $this->parentDir($uri);
        $parentDir->deleteFile(self::entryName($uri));
        return true;
    }

    public function rename(string $oldEntryUri, string $newEntryUri): bool {
        $entry = $this->parentDir($oldEntryUri)->unregisterEntry(self::entryName($oldEntryUri));
        $entry->setUri($newEntryUri);
        $this->parentDir($newEntryUri)->registerEntry($entry);
        clearstatcache(true, $oldEntryUri);
        clearstatcache(true, $newEntryUri);
        return true;
    }

    public function mkdir(string $uri, int $mode, int $flags): bool {
        $parentDirUri = self::parentDirUri($uri);
        $parentDir = $this->root()->dirByUriOrNone($parentDirUri);
        if ($parentDir && $parentDir->dirExists(self::entryName($uri))) {
            throw new \RuntimeException('Unable to create directory, such directory already exists');
        }
        $recursive = boolval($flags & STREAM_MKDIR_RECURSIVE);
        $stat = new VfsEntryStat([
            'mode' => $this->dirMode($mode),
        ]);
        if ($recursive) {
            $this->root()->createAllDirs($uri, $stat);
            clearstatcache();
        } else {
            $parentDir->createDir($uri, $stat);
            clearstatcache(true, $uri);
        }
        return true;
    }

    public function rmdir(string $uri, int $flags): bool {
        $recursive = boolval($flags & STREAM_MKDIR_RECURSIVE);
        if ($recursive) {
            throw new NotImplementedException();
        }
        $parentDir = $this->parentDir($uri);
        $parentDir->deleteDir(self::entryName($uri));
        clearstatcache(true, $uri);
        return true;
    }

    /**
     * @return array|false
     */
    public function url_stat(string $uri, int $flags) {
        $entry = $this->root()->entryByUriOrNone($uri);
        if (!$entry) {
            return false;
        }
        return $entry->stat()->getArrayCopy();
    }

    public function dir_opendir(string $uri, int $flags): bool {
        $dir = $this->dir = $this->root()->dirByUri($uri);
        $dir->open();
        return true;
    }

    /**
     * @return string|false Returns false if there is no next file.
     */
    public function dir_readdir() {
        if ($this->dir) {
            $dir = $this->dir;
            $current = $dir->current();
            if (!$current) {
                return false;
            }
            $entry = $current->name();
            $dir->next();
            return $entry;
        }
        return false;
    }

    public function dir_rewinddir(): bool {
        throw new NotImplementedException();
        // d(func_get_args());
    }

    public function dir_closedir(): bool {
        if ($this->dir) {
            if ($this->dir->isOpen()) {
                $this->dir->close();
            }
        }
        return true;
    }

    public static function prefixUri(string $uri = ''): string {
        return self::URI_PREFIX . $uri;
    }

    public static function stripUriPrefix(string $uri): string {
        $prefix = self::prefixUri();
        if (!startsWith($uri, $prefix)) {
            throw new \UnexpectedValueException();
        }
        $uri = substr($uri, strlen($prefix));
        return $uri;
    }

    public static function parentDirUri(string $uri): string {
        $prefix = self::URI_PREFIX;
        return $prefix . dirname(substr($uri, strlen($prefix)));
    }

    public static function entryName(string $uri): string {
        if ($uri === '') {
            throw new \UnexpectedValueException('Empty URI');
        }
        $uriNoPrefix = self::stripUriPrefix($uri);
        if ($uriNoPrefix === '' || $uriNoPrefix[0] !== '/') {
            throw new \UnexpectedValueException('Invalid path');
        }
        if ($uriNoPrefix === '/') {
            throw new \UnexpectedValueException('Unable to get name for the root');
        }
        return basename($uriNoPrefix);
    }

    protected function checkUri(string $uri): void {
        if (!startsWith($uri, self::URI_PREFIX)) {
            throw new \RuntimeException('Invalid URI');
        }
        if (preg_match('~^(' . self::SCHEME . '://[^/]|://$)~si', $uri)) {
            throw new \RuntimeException('Relative URIs are not supported');
        }
    }

    protected function root(): VfsRoot {
        if (null === self::$root) {
            self::$root = new VfsRoot(self::URI_PREFIX . '/', new VfsEntryStat(['mode' => $this->dirMode()]));
        }
        return self::$root;
    }

    private function parentDir(string $uri): VfsDir {
        return $this->root()->dirByUri(self::parentDirUri($uri));
    }

    private function fileMode($mode = Stat::FILE_BASE_MODE): int {
        return ($mode & ~umask()) | Stat::FILE;
    }

    private function dirMode($mode = Stat::DIR_BASE_MODE): int {
        return ($mode & ~umask()) | Stat::DIR;
    }
}