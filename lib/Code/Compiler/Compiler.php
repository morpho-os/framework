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

abstract class Compiler implements IFn {
    /**
     * @var null|\ArrayObject
     */
    protected $context;

    /**
     * @var array|null
     */
    private $config;

    public function __construct(array $config = null) {
        if ($config) {
            $this->setConfig($config);
        }
    }

    public function setConfig(array $config): void {
        $this->config = $this->checkConfig($config);
    }

    public function __invoke($source) {
        $context = new \ArrayObject([
            'source' => $source,
            'compiler' => $this,
        ]);
        $this->context = $context;
        $frontEnd = $this->mkFrontEnd($this->config['frontEndPhases']);
        $middleEnd = $this->mkMiddleEnd($this->config['middleEndPhases']);
        $backEnd = $this->mkBackEnd($this->config['backEndPhases']);
        return (new Pipe([$frontEnd, $middleEnd, $backEnd]))($context);
    }

    abstract protected function mkFrontEnd($config): FrontEnd;

    abstract protected function mkMiddleEnd($config): MiddleEnd;

    abstract protected function mkBackEnd($config): BackEnd;

    protected function checkConfig(array $config): array {
        $requiredKeys = ['frontEndPhases', 'middleEndPhases', 'backEndPhases'];
        $intersection = array_intersect_key($config, array_flip($requiredKeys));
        if (count($intersection) !== count($requiredKeys)) {
            throw new \RuntimeException('The following required config items are missing: ' . implode(', ', array_diff($requiredKeys, array_keys($config))));
        }
        return $intersection;
    }
}
