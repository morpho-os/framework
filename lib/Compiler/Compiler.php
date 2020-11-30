<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

use Morpho\Base\Pipe;

class Compiler extends Pipe {
    public function __construct($conf = null) {
        $phases = [
            'frontEnd' => $conf['frontEnd'] ?? $this->mkFrontEnd(),
            'middleEnd' => $conf['middleEnd'] ?? $this->mkMiddleEnd(),
            'backEnd' => $conf['backEnd'] ?? $this->mkBackEnd(),
        ];
        parent::__construct($phases);
    }

    public function frontEnd(): callable {
        return $this['frontEnd'];
    }

    public function middleEnd(): callable {
        return $this['middleEnd'];
    }

    public function backEnd(): callable {
        return $this['backEnd'];
    }

    protected function mkFrontEnd(): callable {
        return fn ($v) => $v;
    }

    protected function mkMiddleEnd(): callable {
        return fn ($v) => $v;
    }

    protected function mkBackEnd(): callable {
        return fn ($v) => $v;
    }
}
