<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Caching;

use function Morpho\Base\typeOf;
use Morpho\Caching\VarExportFileCache;
use Morpho\Test\TestCase;

class VarExportFileCacheTest extends TestCase {
    public function dataForCaching() {
        yield [['foo' => 'bar']];
        yield [false];
        yield [true];
        yield [null];
        yield ['Hello World'];
        yield [3.14];
    }

    /**
     * @dataProvider dataForCaching
     */
    public function testCaching($data) {
        $cache = new VarExportFileCache($this->createTmpDir());

        $key = 'my-val';
        $this->assertFalse($cache->has($key));
        $this->assertNull($cache->get($key));
        $this->assertSame('abc', $cache->get($key, 'abc'));
        $this->assertTrue($cache->set('my-val', $data));
        if (is_object($data)) {
            $this->assertEquals($data, $cache->get($key));
        } else {
            $this->assertSame($data, $cache->get($key));
        }
        $this->assertTrue($cache->delete($key));
        $this->assertFalse($cache->has($key));
        $this->assertNull($cache->get($key));
        $def = new \stdClass();
        $this->assertSame($def, $cache->get($key, $def));
    }

    public function dataForThrowsExceptionOnNotSupportedDataType() {
        yield [new \ArrayIterator([])];
        yield [STDIN];
    }

    /**
     * @dataProvider dataForThrowsExceptionOnNotSupportedDataType
     */
    public function testThrowsExceptionOnNotSupportedDataType($data) {
        $cache = new VarExportFileCache($this->createTmpDir());
        $this->expectException(\RuntimeException::class, 'Only arrays and scalars are supported by this class, but $data has type ' . typeOf($data));
        $cache->set('foo', $data);
    }
}