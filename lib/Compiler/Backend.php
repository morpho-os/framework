<?php declare(strict_types=1);
namespace Morpho\Compiler;

use Morpho\Base\NotImplementedException;

class Backend implements IBackend {
    public function __invoke(mixed $context): mixed {
        do {
            $context = $this->optimizer($context);
            $context = $this->codegen($context);
        } while (!$this->done($context));
    }

    public function optimizer(mixed $context): callable {
        throw new NotImplementedException();
    }

    public function codegen(mixed $context): callable {
        throw new NotImplementedException();
    }

    private function done(mixed $context): bool {
        return true;
    }
}