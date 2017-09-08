<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Test;

use Morpho\Db\Sql\Db;
use Morpho\Fs\Directory;

trait TDbTestCase {
    protected function createFixtures($db): void {
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
            'db'       => 'test',
        ];
    }

    protected function newDbConnection($config = null): Db {
        if (!$config) {
            $config = $this->dbConfig();
        }
        return Db::connect($config);
    }
}