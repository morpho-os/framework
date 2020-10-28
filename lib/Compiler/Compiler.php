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
    protected array $conf;

    /**
     * @param array|\ArrayObject|null $conf
     *     factory: IFactory
     */
    public function __construct($conf = null) {
        $this->conf = (array) $conf;
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
        $factory = $this->mkFactory();
        return new Pipe([$factory->mkFrontEnd(), $factory->mkMiddleEnd(), $factory->mkBackEnd()]);
    }

    protected function mkFactory(): IComponentFactory {
        if (isset($this->conf['factory'])) {
            return $this->conf['factory'];
        }
        return new class implements IComponentFactory {
            public function mkFrontEnd(): callable {
                return fn ($context) => $context;
            }

            public function mkMiddleEnd(): callable {
                return fn ($context) => $context;
            }

            public function mkBackEnd(): callable {
                return fn ($context) => $context;
            }
        };
    }
}
