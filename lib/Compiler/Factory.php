<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

class Factory implements IFactory {
    public function mkFrontEnd(): callable {
        return function ($context) {
            return $context;
        };
    }

    public function mkMiddleEnd(): callable {
        return function ($context) {
            return $context;
        };
    }

    public function mkBackEnd(): callable {
        return function ($context) {
            return $context;
        };
    }
}
