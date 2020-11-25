<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\MySql\SelectQuery;
use UnexpectedValueException;

class SelectQueryTest extends DbTestCase {
    use TUsingNorthwind;

    private SelectQuery $select;

    public function setUp(): void {
        parent::setUp();
        $this->select = new SelectQuery($this->db);
    }

    public function testWithoutFromColumns() {
        $columns = "MICROSECOND('2019-12-31 23:59:59.000010'), NOW()";
        $this->assertSqlEquals("SELECT " . $columns, (string) $this->select->columns($this->select->expr($columns)));
    }

    public function testWithFromColumns() {
        $this->assertSqlEquals("SELECT * FROM `cars`", (string) $this->select->from('cars'));
    }

    public function testCompleteSelect() {
        $this->select->columns(['customers.id'])
            ->from(
                $this->select->expr('customers INNER JOIN orders ON customers.id = orders.customer_id')
            );
        $this->assertSqlEquals('SELECT `customers`.`id` FROM customers INNER JOIN orders ON customers.id = orders.customer_id', $this->select);
            /*
            todo
            ->where()
            ->groupBy()
            ->having()
            ->window()
            ->orderBy()
            ->limit()
            ->offset()
            ->for()
            ->__toString();
            */

        //        ->eval()
                //->rows()
        //);
    }

    public function testWhereClause_OnlyCondition_ValidArg() {
        $this->assertSqlEquals("SELECT * WHERE `foo` = 'abc' AND `bar` = 'efg'", $this->select->where(['foo' => 'abc', 'bar' => 'efg']));
    }

    public function testWhereClause_OnlyCondition_InvalidArg() {
        $this->expectException(UnexpectedValueException::class);
        $this->select->where(['foo', 'bar']);
    }
}
