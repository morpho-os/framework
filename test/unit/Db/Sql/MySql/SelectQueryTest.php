<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\Result;
use Morpho\Db\Sql\SelectQuery;
use Morpho\Test\DbTestCase;

class SelectQueryTest extends DbTestCase {
    public function testQueryWithWhere() {
        $query = new SelectQuery($this->newDbConnection());
        $this->assertSame(
            'SELECT * FROM `test` WHERE `ab` = \'cd\' AND `foo` = -123',
            $query->from('test')->where(['ab' => 'cd', 'foo' => -123])->dump()
        );
    }

    public function testQueryWithoutWhere() {
        $query = new SelectQuery($this->newDbConnection());
        $this->assertSame('SELECT * FROM `foo`', $query->from('foo')->dump());
    }

    public function testEval() {
        $res = (new SelectQuery($this->newDbConnection()))
            ->columns('123')
            ->eval();
        $this->assertInstanceOf(Result::class, $res);
        $this->assertSame('123', $res->cell());
    }
}