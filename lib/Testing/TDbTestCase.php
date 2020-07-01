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

    protected function dbConf(): array {
        return [
            'driver'   => 'mysql',
            'host'     => getenv('MORPHO_TEST_DB_HOST') ?: '127.0.0.1',
            'user'     => getenv('MORPHO_TEST_DB_USER') ?: 'root',
            'password' => getenv('MORPHO_TEST_DB_PASSWORD') ?: '',
            'db'       => getenv('MORPHO_TEST_DB_DB') ?: 'test',
        ];
    }

    protected function mkDbClient(array $conf = null): DbClient {
        if (!$conf) {
            $conf = $this->dbConf();
        }
        return DbClient::connect($conf);
    }

    protected function mkPdo(array $conf = null): \PDO {
        if (!$conf) {
            $conf = $this->dbConf();
        }
        $dsn = $conf['driver'] . ':dbname=' . $conf['db'] . ';' . $conf['host'] . ';' . ($conf['charset'] ?? 'utf8');
        return new \PDO($dsn, $conf['user'], $conf['password'], $conf['pdoConf'] ?? []);
    }
}
