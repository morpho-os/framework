<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler;

use Morpho\Base\IFn;
use Morpho\Base\Pipe;
use Morpho\Code\Compiler\BackEnd\BackEnd;
use Morpho\Code\Compiler\FrontEnd\FrontEnd;
use Morpho\Code\Compiler\MiddleEnd\MiddleEnd;

class Compiler implements IFn {
    /**
     * @var null|\ArrayObject
     */
    protected $context;

    /**
     * @var array|null
     */
    protected $config;

    public function __construct(array $config = null) {
        if ($config) {
            $this->setConfig($config);
        }
    }

    public function setConfig(array $config): void {
        $this->config = $this->checkConfig($config);
    }

    public function __invoke($source) {
        $context = $this->mkContext($source);
        $this->context = $context;
        $frontEnd = $this->config['frontEnd']['instance'] ?? $this->mkFrontEnd();
        $middleEnd = $this->config['middleEnd']['instance'] ?? $this->mkMiddleEnd();
        $backEnd = $this->config['backEnd']['instance'] ?? $this->mkBackEnd();
        return (new Pipe([$frontEnd, $middleEnd, $backEnd]))($context);
    }

    protected function mkContext($source): \ArrayObject {
        return new \ArrayObject([
            'source' => $source,
            'compiler' => $this,
        ]);
    }

    protected function mkFrontEnd(): FrontEnd {
        return new FrontEnd($this->config['frontEnd']);
    }

    protected function mkMiddleEnd(): MiddleEnd {
        return new MiddleEnd($this->config['middleEnd']);
    }

    protected function mkBackEnd(): BackEnd {
        return new BackEnd($this->config['backEnd']);
    }

    protected function checkConfig(array $config): array {
/*        $requiredKeys = ['frontEnd', 'middleEnd', 'backEnd'];
        $intersection = array_intersect_key($config, array_flip($requiredKeys));
        if (count($intersection) !== count($requiredKeys)) {
            throw new \RuntimeException('The following required config items are missing: ' . implode(', ', array_diff($requiredKeys, array_keys($config))));
        }
        return $intersection;*/
        return $config;
    }
}
