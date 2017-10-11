<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\MySql\GeneralQuery;
use Morpho\Db\Sql\SelectQuery;
use Morpho\Db\Sql\Result;
use Morpho\Test\DbTestCase;

class QueryTest extends DbTestCase {
    public function dataForWhereClause() {
        return [
            [
                '', [], '', null,
            ],
            [
                ' WHERE `foo` = ? AND `bar` = ?', [123, 'hello'], ['foo' => 123, 'bar' => 'hello'], null
            ],
            [
                ' WHERE `foo` = ? AND `bar` = ?', [123, 'hello'], '`foo` = ? AND `bar` = ?', [123, 'hello']
            ],
        ];
    }


    /**
     * @dataProvider dataForWhereClause
     */
    public function testWhereClause($expectedSql, $expectedArgs, $whereCondition, ?array $whereConditionArgs) {
        $query = new GeneralQuery();
        [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
        $this->assertSame($expectedSql, $whereSql);
        $this->assertSame($expectedArgs, $whereArgs);
    }

    // SelectQuery

    public function testSelectQuery() {
        $query = new SelectQuery($this->newDbConnection());
        $this->assertSame(
            'SELECT * FROM `test` WHERE `ab` = \'cd\' AND `foo` = -123',
            $query->from('test')->where(['ab' => 'cd', 'foo' => -123])->dump()
        );
    }

    public function testSelect_WithoutWhere() {
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