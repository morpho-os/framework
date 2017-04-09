<?php
namespace Morpho\Web\Session;

use Morpho\Base\NotImplementedException;

class DbSessionHandler implements \SessionHandlerInterface {
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