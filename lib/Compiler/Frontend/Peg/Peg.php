<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
/**
 * The implementation is based on Python's PEG:
 * 1. https://medium.com/@gvanrossum_83706/peg-parsing-series-de5d41b2ed60
 * 2. https://www.python.org/dev/peps/pep-0617/
 * 3. https://www.youtube.com/watch?v=QppWTvh7_sI
 */
namespace Morpho\Compiler\Frontend\Peg;

use Morpho\Base\NotImplementedException;
use Morpho\Compiler\Frontend\IGrammar;
use Morpho\Compiler\Frontend\ILexer;
use Morpho\Compiler\Frontend\IParser;
use Morpho\Compiler\Frontend\IProgram;

class Peg implements IGrammar {
    public function rules(): iterable {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * The name is inspired by Python's `make regen-token`
     */
    public function regenLexer(): ILexer {
        return new class implements ILexer {
            public function __invoke(mixed $context): mixed {
                throw new NotImplementedException();
            }
        };
    }

    /**
     * The name is inspired by Python's `make regen-pegen`
     */
    public function regenParser(): IParser {
        return new class implements IParser {
            public function __invoke($context): IProgram {
                throw new NotImplementedException();
            }
        };
    }

}