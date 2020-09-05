<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

use Morpho\Base\IFn;
use Morpho\Base\Pipe;

class Compiler implements IFn {
    protected $conf;

    /**
     * @param array|ArrayObject $conf
     *     factory: IFactory
     */
    public function __construct($conf) {
        $this->conf = $conf;
    }

    public function conf() {
        return $this->conf;
    }

    public function __invoke($context) {
        $pipe = $this->mkPipe($context);
        $context['compiler'] = $this;
        return $pipe($context);
    }

    protected function mkPipe($context): IFn {
        $factory = $this->conf['factory'];
        $frontEnd = $factory->mkFrontEnd();
        $middleEnd = $factory->mkMiddleEnd();
        $backEnd = $factory->mkBackEnd();
        return new Pipe([$frontEnd, $middleEnd, $backEnd]);
    }
}
