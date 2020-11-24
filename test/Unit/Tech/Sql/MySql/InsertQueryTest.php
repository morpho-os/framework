<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\MySql\InsertQuery;

class InsertQueryTest extends DbTestCase {
    public function testInsertQuery() {
        $insert = new InsertQuery($this->db);
        $insert->into('cars')->row(['color' => 'green', 'name' => 'Honda']);
        $rows = $this->pdo->query('SELECT * FROM cars')->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertSame([[
            'name' => 'Honda',
            'color' => 'green',
            'country' => null,
            'type1' => null,
            'type2' => null,
        ]], $rows);
/*
name varchar(20),
color varchar(20),
country varchar(20),
type1 int,
type2 enum('US', 'Japan', 'EU')
 */
    }

    protected function createFixtures($db): void {
        $this->createCarsTable(false);
    }
}
