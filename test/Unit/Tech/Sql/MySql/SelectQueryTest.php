<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\MySql\SelectQuery;

class SelectQueryTest extends DbTestCase {
    use TUsingNorthwind;

    public function testWithoutFromColumns() {
        $select = new SelectQuery($this->db);
        $columns = "MICROSECOND('2019-12-31 23:59:59.000010'), NOW()";
        $this->assertSame("SELECT " . $columns, (string) $select->columns($select->expr($columns)));
    }

    public function testWithFromColumns() {
        $select = new SelectQuery($this->db);
        $this->assertSame("SELECT * FROM `cars`", (string) $select->from('cars'));
    }

    public function testCompleteSelect() {
        $select = new SelectQuery($this->db);
        $select->columns(['customers.id'])
            ->from(
                $select->expr('customers INNER JOIN orders ON customers.id = orders.customer_id')
            );
        $this->assertSame('SELECT `customers`.`id` FROM customers INNER JOIN orders ON customers.id = orders.customer_id', $select->__toString());
            /*
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


}
