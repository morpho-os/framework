<?php
namespace Morpho\Test;

use Morpho\Db\Sql\Db;
use Morpho\Fs\Directory;

abstract class DbTestCase extends TestCase {
    protected function createFixtures($db)/*: void*/ {
        $paths = Directory::listEntries($this->getTestDirPath(), '~Fixture\.php$~');
        foreach ($paths as $path) {
            require_once $path;
            $class = $this->getNamespace(true) . '\\'
                . basename(dirname($path)) . '\\'
                . pathinfo($path, PATHINFO_FILENAME);
            (new $class())->load($db);
        }
    }

    protected function createDbConnection(): \PDO {
        return Db::createConnection($this->getDbConfig());
    }

    protected function getDbConfig(): array {
        return [
            'driver'   => 'mysql',
            'host'     => '127.0.0.1',
            'user'     => 'root',
            'password' => '',
            'db'       => 'test',
        ];
    }

    protected function db($config = null): Db {
        return new Db($config ? $config : $this->createDbConnection());
    }
}
