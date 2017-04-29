<?php
namespace Morpho\Test;

use Morpho\Db\Sql\Db;
use Morpho\Fs\Directory;

abstract class DbTestCase extends TestCase {
    const DB = 'test';
    
    protected function createFixtures($db)/*: void*/ {
        $paths = Directory::paths($this->getTestDirPath(), '~Fixture\.php$~');
        foreach ($paths as $path) {
            require_once $path;
            $class = $this->namespace(true) . '\\'
                . basename(dirname($path)) . '\\'
                . pathinfo($path, PATHINFO_FILENAME);
            (new $class())->load($db);
        }
    }

    protected function dbConfig(): array {
        return [
            'driver'   => 'mysql',
            'host'     => '127.0.0.1',
            'user'     => 'root',
            'password' => '',
            'db'       => self::DB,
        ];
    }

    protected function db($config = null): Db {
        if (!$config) {
            $config = $this->dbConfig();
        }
        return Db::connect($config);
    }
}