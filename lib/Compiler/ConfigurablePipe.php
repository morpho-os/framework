<?php declare(strict_types=1);
namespace Morpho\Compiler;

use Morpho\Base\Pipe;

abstract class ConfigurablePipe extends Pipe {
    protected array $conf;

    public function __construct(array $conf = null) {
        $this->conf = (array) $conf;
    }

    public function setConf(array $conf): self {
        $this->conf = $conf;
        return $this;
    }

    public function conf(): array {
        return $this->conf;
    }

    abstract public function current(): callable;

    abstract public function count(): int;
}