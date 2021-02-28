<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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