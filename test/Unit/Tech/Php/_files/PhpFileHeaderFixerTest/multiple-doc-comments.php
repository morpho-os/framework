<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
/**
 * Some functions are based on functions found at [nikic/iter](https://github.com/nikic/iter) package, Copyright (c) 2013 by Nikita Popov
 */
namespace Morpho\Test\Unit\Tech\PhpFileHeaderFixerTest;

use ArrayObject;
use Closure;
use Generator;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use Stringable;
use Throwable;
use UnexpectedValueException;