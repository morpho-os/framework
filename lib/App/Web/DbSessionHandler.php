<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\NotImplementedException;
use SessionHandlerInterface;

class DbSessionHandler implements SessionHandlerInterface {
    public function close() {
        throw new NotImplementedException();
    }

    public function destroy($sessionId) {
        throw new NotImplementedException();
    }

    public function gc($maxlifetime) {
        throw new NotImplementedException();
    }

    public function open($savePath, $name) {
        throw new NotImplementedException();
    }

    public function read($sessionId) {
        throw new NotImplementedException();
    }

    public function write($sessionId, $sessionData) {
        throw new NotImplementedException();
    }
}
