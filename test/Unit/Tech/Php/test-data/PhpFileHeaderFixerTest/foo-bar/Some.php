<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Php\PhpFileHeaderFixerTest\SomeInvalidNs;

use Morpho\Base\IFn;

class Some implements IFn {
    public function __invoke(mixed $val): mixed {
        return null;
    }
}