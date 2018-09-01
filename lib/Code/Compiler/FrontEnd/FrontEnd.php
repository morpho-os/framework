<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler\FrontEnd;

use Morpho\Code\Compiler\CompilerPhase;
use Morpho\Code\Compiler\ICompiler;

class FrontEnd extends CompilerPhase implements IFrontEnd {
    /**
     * @var IFrontEndFactory
     */
    protected $factory;

    public function __construct(ICompiler $compiler) {
        $this->factory = $compiler->config()['frontEnd']['factory'];
    }

    public function diagnosticMessages(): iterable {
        return [];
    }

    public function getIterator() {
        return $this->factory->mkFrontEndPhases();
    }
}
