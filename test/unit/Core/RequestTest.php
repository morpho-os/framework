<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Cli\Request;
use Morpho\Test\TestCase;

class RequestTest extends TestCase {
    public function testParams() {
        $request = new Request();

        $params = $request->params();
        $this->assertInstanceOf(\ArrayObject::class, $params);
        $params['foo'] = 'bar';
        $this->assertSame(['foo' => 'bar'], $request->params()->getArrayCopy());

        $newParams = new \ArrayObject(['test' => '123']);
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($request->setParams($newParams));
        $this->assertSame($newParams, $request->params());

        $newParams = ['hello' => 456];
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($request->setParams($newParams));
        $this->assertSame($newParams, $request->params()->getArrayCopy());
    }
}