<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\Sqlite;

use Morpho\Db\Sql\Sqlite;
use MorphoTest\Unit\Db\Sql\DbTest as BaseDbTest;

class DbTest extends BaseDbTest {
    public function testConnect_PdoInstanceArgument() {
        $pdo = new \PDO('sqlite::memory:');
        $connection = \Morpho\Db\Sql\Db::connect($pdo);
        $this->assertInstanceOf(Sqlite\Db::class, $connection);
    }
}