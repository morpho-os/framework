<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Base\Conf;
use Morpho\Tech\Sql\DbClient as BaseDbClient;
use PDO;

class DbClient extends BaseDbClient {
    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 3306;
    public const DEFAULT_USER = 'root';
    public const DEFAULT_PASSWORD = '';
    public const DEFAULT_CHARSET = 'utf8';
    public const DEFAULT_DB = '';

    protected function connect($confOrPdo): \PDO {
        if (is_array($confOrPdo)) {
            $conf = Conf::check([
                'host' => self::DEFAULT_HOST,
                'port' => self::DEFAULT_PORT,
                'user' => self::DEFAULT_USER,
                'db' => self::DEFAULT_DB,
                'password' => self::DEFAULT_PASSWORD,
                'charset' => self::DEFAULT_CHARSET,
                'sockFilePath' => null,
                'pdoConf' => null,
            ], $confOrPdo);
            $transportStr = null !== $conf['sockFilePath']
                ? 'unix_socket=' . $conf['sockFilePath']
                : "host={$conf['host']};port={$conf['port']}";
            $dsn = "mysql:$transportStr;dbname={$conf['db']};charset={$conf['charset']}";
            $pdoConf = $conf['pdoConf'] ?? null;
            $pdo = new PDO($dsn, $conf['user'], $conf['password']);
        } else {
            $pdo = $confOrPdo;
            $pdoConf = null;
        }
        if (null === $pdoConf) {
            $pdoConf = $this->pdoConf;
        }
        foreach ($pdoConf as $name => $val) {
            $pdo->setAttribute($name, $val);
        }
        return $pdo;
    }

    public function dbName(): ?string {
        return $this->eval('SELECT DATABASE()')->field();
    }

    public function useDb(string $dbName): void {
        $this->exec('USE ' . $this->quoteIdentifier($dbName));
    }

    protected function quoteIdentifier(string $identifier): string {
        // @see http://dev.mysql.com/doc/refman/5.7/en/identifiers.html
        return '`' . $identifier . '`';
    }
}
