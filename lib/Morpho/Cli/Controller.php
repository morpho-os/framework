<?php
namespace Morpho\Cli;

use Morpho\Core\Controller as BaseController;

abstract class Controller extends BaseController {
    public function beforeEach() {
        if (PHP_SAPI !== 'cli') {
            $this->accessDenied();
        }
    }

    protected function accessDenied() {
        throw new AccessDeniedException();
    }
}