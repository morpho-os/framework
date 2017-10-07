<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\MySql\Query;
use Morpho\Db\Sql\MySql\SelectQuery;
use Morpho\Test\TestCase;

class QueryTest extends TestCase {
    public function testSelectQuery() {
        $query = new SelectQuery();
        $this->assertSame(
            'SELECT * FROM `test` WHERE `ab` = \'cd\' AND `foo` = -123',
            $query->from('test')->where(['ab' => 'cd', 'foo' => -123])->dump()
        );
    }

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
        $query = new Query();
        [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
        $this->assertSame($expectedSql, $whereSql);
        $this->assertSame($expectedArgs, $whereArgs);
    }
}