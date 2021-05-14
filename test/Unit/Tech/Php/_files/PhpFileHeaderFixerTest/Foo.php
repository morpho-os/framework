<?php declare(strict_types=1);

namespace Morpho\Test\Unit\Tech\Php\PhpFileHeaderFixerTest;

use Morpho\Base\IFn;

class Foo implements IFn {
    public function __invoke(mixed $val): mixed {
        return null;
    }
}