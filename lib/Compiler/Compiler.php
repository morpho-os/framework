<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

use UnexpectedValueException;

class Compiler extends ConfigurablePipe implements ICompiler {
    /**
     * @var callable
     */
    private $frontend;
    /**
     * @var callable
     */
    private $midend;
    /**
     * @var callable
     */
    private $backend;

    public function conf(): array {
        return $this->conf;
    }

    public function current(): callable {
        $index = $this->index;
        if ($index === 0) {
            return $this->frontend();
        }
        if ($index === 1) {
            return $this->midend();
        }
        if ($index === 2) {
            return $this->backend();
        }
        throw new UnexpectedValueException();
    }

    public function count(): int {
        // Valid pipe phases are `[$this->frontend(), $this->midend(), $this->backend()]`, so the count is 3.
        return 3;
    }

    public function setFrontend(callable $frontend): self {
        $this->frontend = $frontend;
        return $this;
    }

    public function frontend(): callable {
        if (null === $this->frontend) {
            $this->frontend = $this->conf['frontend'];
        }
        return $this->frontend;
    }

    public function setMidend(callable $midend): self {
        $this->midend = $midend;
        return $this;
    }

    public function midend(): callable {
        if (null === $this->midend) {
            $this->midend = $this->conf['midend'];
        }
        return $this->midend;
    }

    public function setBackend(callable $backend): self {
        $this->backend = $backend;
        return $this;
    }

    public function backend(): callable {
        if (null === $this->backend) {
            $this->backend = $this->conf['backend'];
        }
        return $this->backend;
    }
}
