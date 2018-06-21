<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler\MiddleEnd;

use Morpho\Code\Compiler\CompilerPhase;

abstract class MiddleEnd extends CompilerPhase {
    public function getIterator() {
        return [
            $this->mkOptimizer(),
        ];
    }

    abstract protected function mkOptimizer(): IOptimizer;
}