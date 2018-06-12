<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Caching;

use Morpho\Caching\ICache;
use Morpho\Testing\TestCase;

abstract class CacheTest extends TestCase {
    public function dataForCaching() {
        yield [['foo' => 'bar']];
        yield [false];
        yield [true];
        yield [null];
        yield ['Hello World'];
        yield [3.14];
        yield [new \ArrayIterator([])];
    }

    /**
     * @dataProvider dataForCaching
     */
    public function testCaching($data) {
        // @TODO: get, set, delete, clear, getMultiple, setMultiple, deleteMultiple, has
        $cache = $this->mkCache();
        $key = 'my-val';
        $this->assertFalse($cache->has($key));
        $this->assertNull($cache->get($key));
        $this->assertSame('abc', $cache->get($key, 'abc'));
        $this->assertTrue($cache->set('my-val', $data));
        if (\is_object($data)) {
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

    abstract protected function mkCache(): ICache;
}
