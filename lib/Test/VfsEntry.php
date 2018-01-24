<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

abstract class VfsEntry implements IVfsEntry {
    /**
     * @var string
     */
    private $uri;

    /**
     * @var bool
     */
    protected $isOpen = false;

    private $stat;

    public function __construct(string $uri, VfsEntryStat $stat) {
        $this->uri = $uri;
        $this->stat = $stat;
    }

    public function name(): string {
        return Vfs::entryName($this->uri);
    }

    public function setUri(string $uri): void {
        $this->uri = $uri;
    }

    public function uri(): string {
        return $this->uri;
    }

    public function __destruct() {
        if ($this->isOpen) {
            $this->close();
        }
    }

    public function close(): void {
        $this->checkIsOpen();
        $this->isOpen = false;
    }

    public function isOpen(): bool {
        return $this->isOpen;
    }

    public function stat(): VfsEntryStat {
        return $this->stat;
    }

    protected function checkIsOpen(): void {
        if (!$this->isOpen) {
            throw new \LogicException('Entry has not been opened');
        }
    }

    protected function normalizeStat(VfsEntryStat $stat): void {
        $now = time();
        if (!isset($stat['mtime'])) {
            $stat['mtime'] = $now;
        }
        if (!isset($stat['atime'])) {
            $stat['atime'] = $now;
        }
        if (!isset($stat['ctime'])) {
            $stat['ctime'] = $now;
        }
    }
}