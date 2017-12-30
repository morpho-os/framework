<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\InsertQuery;
use Morpho\Test\DbTestCase;

class InsertQueryTest extends DbTestCase {
    private $db;

    public function setUp() {
        parent::setUp();
        $this->db = $this->newDbConnection();
        $this->db->eval('DROP TABLE IF EXISTS insertTest');
        $this->db->eval('CREATE TABLE insertTest (foo varchar(255), created int, i int unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`i`));');
    }

    public function tearDown() {
        parent::tearDown();
        $this->db->eval('DROP TABLE IF EXISTS insertTest');
    }

    public function testInsert_SingleRow() {
        $now = time();
        $query = (new InsertQuery($this->newDbConnection()))
            ->table('test')
            ->row(['foo' => 'bar', 'created' => $now]);
        $this->assertSame("INSERT INTO `test` (`foo`, `created`) VALUES ('bar', $now)", $query->dump());
    }
}