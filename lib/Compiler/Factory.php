<?php declare(strict_types=1);
namespace Morpho\Compiler;

class Factory implements IFactory {
    public function mkFrontend(): callable {
        return new Frontend();
    }

    public function mkMidend(): callable {
        // Middle end by default does nothing.
        return fn ($v) => $v;
    }

    public function mkBackend(): callable {
        return new Backend();
    }
}