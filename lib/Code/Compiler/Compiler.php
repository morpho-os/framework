<?php declare(strict_types=1);
namespace Morpho\Code\Compiler;

use Morpho\Base\IFn;
use Morpho\Base\Pipe;
use Morpho\Code\Compiler\BackEnd\BackEnd;
use Morpho\Code\Compiler\FrontEnd\FrontEnd;
use Morpho\Code\Compiler\MiddleEnd\MiddleEnd;

class Compiler implements IFn {
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
        $frontEnd = $this->mkFrontEnd();
        $middleEnd = $this->mkMiddleEnd();
        $backEnd = $this->mkBackEnd();
        return (new Pipe([$frontEnd, $middleEnd, $backEnd]))($context);
    }

    private function mkFrontEnd(): FrontEnd {
        return new FrontEnd($this->config['frontEndPhases']);
    }

    private function mkMiddleEnd(): MiddleEnd {
        return new MiddleEnd($this->config['middleEndPhases']);
    }

    private function mkBackEnd(): BackEnd {
        return new BackEnd($this->config['backEndPhases']);
    }

    protected function checkConfig(array $config): array {
        $requiredKeys = ['frontEndPhases', 'middleEndPhases', 'backEndPhases'];
        $intersection = array_intersect_key($config, array_flip($requiredKeys));
        if (count($intersection) !== count($requiredKeys)) {
            throw new \RuntimeException('The following required config items are missing: ' . implode(', ', array_diff($requiredKeys, array_keys($config))));
        }
        return $intersection;
    }
}
