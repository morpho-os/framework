<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Db\Sql\Sqlite;

use Morpho\Db\Sql\Sqlite;
use Morpho\Test\Unit\Db\Sql\DbClientTest as BaseDbClientTest;

class DbClientTest extends BaseDbClientTest {
    public function testConnect_PdoInstanceArgument() {
        $pdo = new \PDO('sqlite::memory:');
        $connection = \Morpho\Db\Sql\DbClient::connect($pdo);
        $this->assertInstanceOf(Sqlite\DbClient::class, $connection);
    }
}