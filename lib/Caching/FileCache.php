<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * This class based on \Doctrine\Common\Cache\FileCache from Doctrine project
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @author Tobias Schultze <http://tobion.de>
 */
abstract class FileCache extends Cache {
    /**
     * The cache directory.
     *
     * @var string
     */
    protected $dirPath;

    /**
     * The cache file extension.
     *
     * @var string
     */
    private $extension;

    /**
     * @var int
     */
    private $umask;

    /**
     * @var int
     */
    private $dirPathStrLength;

    /**
     * @var int
     */
    private $extensionStrLength;

    /**
     * @var bool
     */
    private $isRunningOnWindows;

    public function __construct(string $dirPath, string $extension, int $umask = 0002) {
        $this->umask = $umask;

        if (!$this->createDirIfNeeded($dirPath)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" does not exist and could not be created.',
                $dirPath
            ));
        }

        if (!is_writable($dirPath)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writable.',
                $dirPath
            ));
        }

        // YES, this needs to be *after* createPathIfNeeded()
        $this->dirPath = realpath($dirPath);
        $this->extension = $extension;

        $this->dirPathStrLength = strlen($this->dirPath);
        $this->extensionStrLength = strlen($this->extension);
        $this->isRunningOnWindows = defined('PHP_WINDOWS_VERSION_BUILD');
    }

    public function delete($key) {
        $filePath = $this->cacheFilePath($key);
        return @unlink($filePath) || !file_exists($filePath);
    }

    public function clear(): bool {
        foreach ($this->dirIter() as $name => $file) {
            if ($file->isDir()) {
                // Remove the intermediate directories which have been created to balance the tree. It only takes effect
                // if the directory is empty. If several caches share the same directory but with different file extensions,
                // the other ones are not removed.
                @rmdir($name);
            } elseif ($this->isFilenameEndingWithExtension($name)) {
                // If an extension is set, only remove files which end with the given extension.
                // If no extension is set, we have no other choice than removing everything.
                @unlink($name);
            }
        }

        return true;
    }

    public function stats(): ?array {
        $usage = 0;
        foreach ($this->dirIter() as $name => $file) {
            if (!$file->isDir() && $this->isFilenameEndingWithExtension($name)) {
                $usage += $file->getSize();
            }
        }

        $free = disk_free_space($this->dirPath);

        return [
            Cache::STATS_HITS             => null,
            Cache::STATS_MISSES           => null,
            Cache::STATS_UPTIME           => null,
            Cache::STATS_MEMORY_USAGE     => $usage,
            Cache::STATS_MEMORY_AVAILABLE => $free,
        ];
    }

    /**
     * Writes a string content to file in an atomic way.
     *
     * @param string $filename Path to the file where to write the data.
     * @param string $content The content to write
     *
     * @return bool TRUE on success, FALSE if path cannot be created, if path is not writable or an any other error.
     */
    protected function writeFile(string $cacheFilePath, string $content): bool {
        $dirPath = pathinfo($cacheFilePath, PATHINFO_DIRNAME);
        if (!$this->createDirIfNeeded($dirPath)) {
            return false;
        }
        if (!is_writable($dirPath)) {
            return false;
        }
        $tmpFilePath = tempnam($dirPath, 'swap');
        @chmod($tmpFilePath, 0666 & (~$this->umask));

        if (file_put_contents($tmpFilePath, $content) !== false) {
            @chmod($tmpFilePath, 0666 & (~$this->umask));
            if (@rename($tmpFilePath, $cacheFilePath)) {
                return true;
            }
            @unlink($tmpFilePath);
        }
        return false;
    }

    protected function cacheFilePath(string $key): string {
        $hash = hash('sha256', $key);

        // This ensures that the filename is unique and that there are no invalid chars in it.
        if ('' === $key
            || ((strlen($key) * 2 + $this->extensionStrLength) > 255)
            || ($this->isRunningOnWindows && ($this->dirPathStrLength + 4 + strlen($key) * 2 + $this->extensionStrLength) > 258)
        ) {
            // Most filesystems have a limit of 255 chars for each path component. On Windows the the whole path is limited
            // to 260 chars (including terminating null char). Using long UNC ("\\?\" prefix) does not work with the PHP API.
            // And there is a bug in PHP (https://bugs.php.net/bug.php?id=70943) with path lengths of 259.
            // So if the id in hex representation would surpass the limit, we use the hash instead. The prefix prevents
            // collisions between the hash and bin2hex.
            $filename = '_' . $hash;
        } else {
            $filename = bin2hex($key);
        }

        return $this->dirPath
            . DIRECTORY_SEPARATOR
            . substr($hash, 0, 2)
            . DIRECTORY_SEPARATOR
            . $filename
            . $this->extension;
    }

    /**
     * @return \Iterator
     */
    private function dirIter(): \Iterator {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dirPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    /**
     * @param string $name The filename
     *
     * @return bool
     */
    private function isFilenameEndingWithExtension(string $name): bool {
        return '' === $this->extension
            || strrpos($name, $this->extension) === (strlen($name) - $this->extensionStrLength);
    }

    /**
     * Create path if needed.
     *
     * @param string $path
     * @return bool TRUE on success or if path already exists, FALSE if path cannot be created.
     */
    private function createDirIfNeeded(string $path): bool {
        if (!is_dir($path)) {
            if (false === @mkdir($path, 0777 & (~$this->umask), true) && !is_dir($path)) {
                return false;
            }
        }
        return true;
    }
}
