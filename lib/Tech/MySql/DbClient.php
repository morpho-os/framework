<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\MySql;

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

    public function connect(array $conf): void {
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
            $conf
        );
        $transportStr = null !== $conf['sockFilePath'] ? 'unix_socket=' . $conf['sockFilePath'] : "host={$conf['host']};port={$conf['port']}";
        $dsn = "mysql:{$transportStr};dbname={$conf['db']};charset={$conf['charset']}";
        $pdo = new PDO($dsn, $conf['user'], $conf['password']);
        foreach ($conf['pdoConf'] ?? $this->pdoConf as $name => $val) {
            $pdo->setAttribute($name, $val);
        }
        $this->pdo = $pdo;
    }

    public function insert(array $spec = null): IQuery {
        return new InsertQuery($this, $spec);
    }

    public function select(array $spec = null): IQuery {
        return new SelectQuery($this, $spec);
    }

    public function update(array $spec = null): IQuery {
        return new UpdateQuery($this, $spec);
    }

    public function delete(array $spec = null): IQuery {
        return new DeleteQuery($this, $spec);
    }

    public function replace(array $spec = null): IQuery {
        return new ReplaceQuery($this, $spec);
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