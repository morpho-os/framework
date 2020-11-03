<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\Conf;
use Morpho\Db\Sql\DbClient as BaseDbClient;
use Morpho\Db\Sql\ReplaceQuery;
use Morpho\Db\Sql\Schema as BaseSchema;
use Morpho\Db\Sql\GeneralQuery as BaseGeneralQuery;
use PDO;
use function array_keys;
use function array_merge;
use function array_values;
use function count;
use function implode;
use function ltrim;
use function str_repeat;

class DbClient extends BaseDbClient {
    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 3306;
    public const DEFAULT_USER = 'root';
    public const DEFAULT_PASSWORD = '';
    public const DEFAULT_CHARSET = 'utf8';
    public const DEFAULT_DB = '';

    private $schema;

    private $query;

    public function query(): BaseGeneralQuery {
        if (null === $this->query) {
            $this->query = new GeneralQuery();
        }
        return $this->query;
    }

    public function schema(): BaseSchema {
        if (null === $this->schema) {
            $this->schema = new Schema($this);
        }
        return $this->schema;
    }

    // @TODO: Move to Query
    public function insertRows(string $tableName, array $rows, array $conf = null): void {
        $args = [];
        $keys = null;
        foreach ($rows as $row) {
            if (null === $keys) {
                $keys = array_keys($row);
            }
            $args = array_merge($args, array_values($row));
        }
        $query = $this->query();
        $valuesClause = ', (' . implode(', ', $query->positionalPlaceholders($keys)) . ')';
        $sql = 'INSERT INTO ' . $query->quoteIdentifier($tableName) . ' (' . implode(', ', $query->quoteIdentifiers($keys)) . ') VALUES ' . ltrim(str_repeat($valuesClause, count($rows)), ', ');
        $this->eval($sql, $args);
    }

    public function mkReplaceQuery(): ReplaceQuery {
        return new ReplaceQuery($this);
    }

    protected function mkPdo($conf, $pdoConf): PDO {
        $conf = Conf::check([
            'host' => self::DEFAULT_HOST,
            'port' => self::DEFAULT_PORT,
            'user' => self::DEFAULT_USER,
            'db' => self::DEFAULT_DB,
            'password' => self::DEFAULT_PASSWORD,
            'charset' => self::DEFAULT_CHARSET,
            'sockFilePath' => null,
        ], $conf);
        $transportStr = null !== $conf['sockFilePath']
            ? 'unix_socket=' . $conf['sockFilePath']
            : "host={$conf['host']};port={$conf['port']}";
        $dsn = self::MYSQL_DRIVER . ":$transportStr;dbname={$conf['db']};charset={$conf['charset']}";
        return new PDO($dsn, $conf['user'], $conf['password'], $pdoConf);
    }
}
