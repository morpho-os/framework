<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Db\Sql\DbClient;
use Morpho\Fs\Dir;

trait TDbTestCase {
    protected function createFixtures($db): void {
        $paths = Dir::paths($this->getTestDirPath(), '~Fixture\.php$~');
        foreach ($paths as $path) {
            require_once $path;
            $class = $this->namespace(true) . '\\'
                . \basename(\dirname($path)) . '\\'
                . \pathinfo($path, PATHINFO_FILENAME);
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

    protected function newDbClient(array $config = null): DbClient {
        if (!$config) {
            $config = $this->dbConfig();
        }
        return DbClient::connect($config);
    }

    protected function newPdo(array $config = null): \PDO {
        if (!$config) {
            $config = $this->dbConfig();
        }
        $dsn = $config['driver'] . ':dbname=' . $config['db'] . ';' . $config['host'] . ';' . ($config['charset'] ?? 'utf8');
        return new \PDO($dsn, $config['user'], $config['password'], $config['pdoConfig'] ?? []);
    }
}
