<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler;

use Morpho\Base\Config;
use Morpho\Base\Pipe;

class Compiler implements ICompiler {
    /**
     * @var Config|null
     */
    protected $config;

    public function __construct(Config $config = null) {
        $this->config = $config;
    }

    public function config(): Config {
        return $this->config;
    }

    public function __invoke($sourceProgram) {
        $context = $this->mkContext($sourceProgram);
        /** @var ICompilerFactory $factory */
        $factory = $this->config['compiler']['factory'];
        $factory->setCompiler($this);
        $frontEnd = $factory->mkFrontEnd();
        $middleEnd = $factory->mkMiddleEnd();
        $backEnd = $factory->mkBackEnd();
        return (new Pipe([$frontEnd, $middleEnd, $backEnd]))($context);
    }

    protected function mkContext($sourceProgram): \ArrayObject {
        return new \ArrayObject([
            'source' => $sourceProgram,
            'compiler' => $this,
        ]);
    }
}
