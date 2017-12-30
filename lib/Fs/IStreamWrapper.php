<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

/**
 * Defines a generic PHP stream wrapper interface.
 *
 * @see http://www.php.net/manual/class.streamwrapper.php
 */
interface IStreamWrapper {
    public function stream_open($uri, $mode, $options, &$openedUri);

    public function stream_close();

    public function stream_lock($operation);

    /**
     * @param  int $count
     * @return string
     */
    public function stream_read($count);

    public function stream_write($data);

    public function stream_eof();

    public function stream_seek($offset, $whence);

    public function stream_flush();

    public function stream_tell();

    public function stream_stat();

    public function unlink($uri);

    public function rename($fromUri, $toUri);

    public function mkdir($uri, $mode, $options);

    public function rmdir($uri, $options);

    public function url_stat($uri, $flags);

    public function dir_opendir($uri, $options);

    public function dir_readdir();

    public function dir_rewinddir();

    public function dir_closedir();
}
