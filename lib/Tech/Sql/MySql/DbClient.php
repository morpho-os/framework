<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Base\Conf;
use Morpho\Tech\Sql\DbClient as BaseDbClient;
use Morpho\Tech\Sql\IQuery;
use Morpho\Tech\Sql\ISchema;
use PDO;

class DbClient extends BaseDbClient {
    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 3306;
    public const DEFAULT_USER = 'root';
    public const DEFAULT_PASSWORD = '';
    public const DEFAULT_CHARSET = 'utf8';
    public const DEFAULT_DB = '';

    protected string $quote = '`';

    private ?ISchema $schema = null;

    public function insert($spec = null): IQuery {
        return new InsertQuery($this, $spec);
    }

    public function select($spec = null): IQuery {
        return new SelectQuery($this, $spec);
    }

    public function update($spec = null): IQuery {
        return new UpdateQuery($this, $spec);
    }

    public function delete($spec = null): IQuery {
        return new DeleteQuery($this, $spec);
    }

    public function replace($spec = null): IQuery {
        return new ReplaceQuery($this, $spec);
    }

    protected function connect($confOrPdo): \PDO {
        if (is_array($confOrPdo)) {
            $conf = Conf::check(
                [
                    'host'         => self::DEFAULT_HOST,
                    'port'         => self::DEFAULT_PORT,
                    'user'         => self::DEFAULT_USER,
                    'db'           => self::DEFAULT_DB,
                    'password'     => self::DEFAULT_PASSWORD,
                    'charset'      => self::DEFAULT_CHARSET,
                    'sockFilePath' => null,
                    'pdoConf'      => null,
                ],
                $confOrPdo
            );
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

    public function useDb(string $dbName): self {
        $this->exec('USE ' . $this->quoteIdentifier($dbName));
        return $this;
    }

    public function schema(): ISchema {
        if (null === $this->schema) {
            $this->schema = new Schema($this);
        }
        return $this->schema;
    }
}
