<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\Uri;

use Morpho\Test\TestCase;
use Morpho\Web\Uri\Query;

class QueryTest extends TestCase {
    public function testNonEmptyConstructorArg() {
        $query = new Query('first=value&arr[]=foo bar&arr[test]=baz');
        $this->assertSame('value', $query['first']);
        $this->assertSame(['foo bar', 'test' => 'baz'], $query['arr']);
        $this->assertCount(2, $query);
    }

    public function testEmptyConstructorArg() {
        $query = new Query('');
        $this->assertFalse($query->isNull());
        $this->assertSame('', $query->toStr());
    }

    public function testQueryArgWithoutValueOrWithEmptyValue() {
        $query = new Query('foo');
        $this->assertSame('foo', $query->toStr());

        $query = new Query('foo=');
        $this->assertSame('foo=', $query->toStr());
    }

    public function testQuery() {
        $query = new Query();

        $this->assertTrue($query->isNull());
        $this->assertSame('', $query->toStr());

        $query['foo'] = 'bar';
        $this->assertFalse($query->isNull());

        $query['has space'] = 'тест';
$this->assertSame('foo=bar&has%20space=%D1%82%D0%B5%D1%81%D1%82', $query->toStr());

        unset($query['foo']);
        $this->assertSame('has%20space=%D1%82%D0%B5%D1%81%D1%82', $query->toStr());
        $this->assertFalse($query->isNull());

        unset($query['has space']);
        $this->assertTrue($query->isNull());
        $this->assertSame('', $query->toStr());
    }
}