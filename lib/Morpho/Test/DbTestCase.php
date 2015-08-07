<?php
namespace Morpho\Test;

use Morpho\Db\Db;
use Morpho\Fs\Directory;

abstract class DbTestCase extends TestCase {
    protected function createFixtures($db) {
        $paths = Directory::listEntries($this->getTestDirPath(), '~Fixture\.php$~');
        foreach ($paths as $path) {
            require_once $path;
            $class = $this->getNamespace(true) . '\\'
                . basename(dirname($path)) . '\\'
                . pathinfo($path, PATHINFO_FILENAME);
            (new $class())->load($db);
        }
    }

    protected function createDbConnection() {
        return Db::createConnection($this->getDbConfig());
    }

    protected function getDbConfig() {
        return array(
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'user' => 'root',
            'password' => '',
            'db' => 'test',
        );
    }

    protected function createDb($config = null) {
        return new Db($config ? $config : $this->createDbConnection());
    }
}
