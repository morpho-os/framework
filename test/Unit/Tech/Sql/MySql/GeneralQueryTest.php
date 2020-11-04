<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\MySql\GeneralQuery;
use Morpho\Testing\DbTestCase;

class GeneralQueryTest extends DbTestCase {
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
}