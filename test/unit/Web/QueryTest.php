<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Query;

class QueryTest extends TestCase {
    public function testQueryStrAsConstructorArg() {
        $query = new Query('first=value&arr[]=foo bar&arr[test]=baz');
        $this->assertSame('value', $query['first']);
        $this->assertSame(['foo bar', 'test' => 'baz'], $query['arr']);
        $this->assertCount(2, $query);
    }

    public function testQueryArgWithoutValueOrWithEmptyValue() {
        $query = new Query('foo');
        $this->assertSame('foo', $query->toString());

        $query = new Query('foo=');
        $this->assertSame('foo=', $query->toString());
    }

    public function testQuery() {
        $query = new Query();
        $this->assertInstanceOf(\ArrayObject::class, $query);
        $this->assertTrue($query->isEmpty());
        $this->assertSame('', $query->toString());
        $query['foo'] = 'bar';
        $this->assertFalse($query->isEmpty());
        $query['has space'] = 'тест';
$this->assertSame('foo=bar&has%20space=%D1%82%D0%B5%D1%81%D1%82', $query->toString());
        unset($query['foo']);
        $this->assertSame('has%20space=%D1%82%D0%B5%D1%81%D1%82', $query->toString());
        $this->assertFalse($query->isEmpty());
        unset($query['has space']);
        $this->assertTrue($query->isEmpty());
        $this->assertSame('', $query->toString());
    }
}