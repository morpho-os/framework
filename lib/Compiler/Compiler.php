<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

use Morpho\Base\Pipe;
use UnexpectedValueException;

class Compiler extends Pipe implements ICompiler {
    protected array $conf;
    private $frontEnd;
    private $middleEnd;
    private $backEnd;

    public function __construct(array $conf = null) {
        if (isset($conf['frontEnd'])) {
            $this->frontEnd = $conf['frontEnd'];
        }
        if (isset($conf['middleEnd'])) {
            $this->middleEnd = $conf['middleEnd'];
        }
        if (isset($conf['backEnd'])) {
            $this->backEnd = $conf['backEnd'];
        }
        $this->conf = (array) $conf;
    }

    public function setConf(array $conf): self {
        $this->conf = $conf;
        return $this;
    }

    public function conf(): array {
        return $this->conf;
    }

    public function current(): callable {
        $index = $this->index;
        if ($index === 0) {
            return $this->frontEnd();
        }
        if ($index === 1) {
            return $this->middleEnd();
        }
        if ($index === 2) {
            return $this->backEnd();
        }
        throw new UnexpectedValueException();
    }

    public function count(): int {
        // Valid pipe phases are `[$this->frontEnd(), $this->middleEnd(), $this->backEnd()]`, so the count is 3.
        return 3;
    }

    public function setFrontEnd(callable $frontEnd): self {
        $this->frontEnd = $frontEnd;
        return $this;
    }

    public function frontEnd(): callable {
        if (null === $this->frontEnd) {
            $this->frontEnd = $this->mkFrontEnd();
        }
        return $this->frontEnd;
    }

    public function setMiddleEnd(callable $middleEnd): self {
        $this->middleEnd = $middleEnd;
        return $this;
    }

    public function middleEnd(): callable {
        if (null === $this->middleEnd) {
            $this->middleEnd = $this->mkMiddleEnd();
        }
        return $this->middleEnd;
    }

    public function setBackEnd(callable $backEnd): self {
        $this->backEnd = $backEnd;
        return $this;
    }

    public function backEnd(): callable {
        if (null === $this->backEnd) {
            $this->backEnd = $this->mkBackEnd();
        }
        return $this->backEnd;
    }

    protected function mkFrontEnd(): callable {
        return fn ($v) => $v;
    }

    protected function mkMiddleEnd(): callable {
        return fn ($v) => $v;
    }

    protected function mkBackEnd(): callable {
        return fn ($v) => $v;
    }
}
