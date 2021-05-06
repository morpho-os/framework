<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Fs\Dir;

trait TDbTestCase {
    protected function createFixtures($db): void {
        $testDirPath = $this->getTestDirPath();
        if (is_dir($testDirPath)) {
            $paths = Dir::paths($testDirPath, '~Fixture\.php$~');
            foreach ($paths as $path) {
                require_once $path;
                $class = $this->ns() . '\\'
                    . \basename(\dirname($path)) . '\\'
                    . \pathinfo($path, PATHINFO_FILENAME);
                (new $class())->load($db);
            }
        }
    }

    protected function dbConf(): array {
        return $this->sut()->dbConf();
    }

    protected function mkPdo(array $conf = null): \PDO {
        if (!$conf) {
            $conf = $this->dbConf();
        }
        $dsn = $conf['driver'] . ':dbname=' . $conf['db'] . ';' . $conf['host'] . ';' . ($conf['charset'] ?? 'utf8');
        return new \PDO($dsn, $conf['user'], $conf['password'], $conf['pdoConf'] ?? []);
    }

    abstract protected function sut(): mixed;

    abstract protected function ns(): string;
}