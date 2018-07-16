<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler\BackEnd;

use Morpho\Code\Compiler\CompilerPhase;

class BackEnd extends CompilerPhase implements IBackEnd {
    public function getIterator() {
        yield from [
            $this->mkCodeGen(),
        ];
    }

    protected function mkCodeGen(): ICodeGen {
        return new class implements ICodeGen {
            public function __invoke($context) {
                return $context['source'];
            }
        };
    }
}
