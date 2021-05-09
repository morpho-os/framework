<?php declare(strict_types=1);

namespace Morpho\Test\Unit\Tech\Fix\PhpFileHeaderFixerTest;

use Morpho\Base\Err;
use Morpho\Base\IFn;
use Morpho\Base\Ok;
use Morpho\Base\Result;
use Morpho\Fs\Path;
use Morpho\Tech\Php\Reflection\ClassTypeDiscoverer;
use Morpho\Tech\Php\Reflection\FileReflection;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

use function Morpho\Base\init;
use function Morpho\Base\last;
use function Morpho\Base\q;
use function Morpho\Tech\Php\parse;
use function Morpho\Tech\Php\parseFile;
use function Morpho\Tech\Php\ppFile;
use function Morpho\Tech\Php\visit;
use function Morpho\Tech\Php\visitFile;

class Foo implements IFn {
}