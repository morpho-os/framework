<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql;

/**
 * @method \PDOStatement prepare($statement, array $driver_options = array())
 * @method bool beginTransaction()
 * @method bool commit()
 * @method bool rollBack()
 * @method bool setAttribute($attribute, $value)
 * @method int exec($statement)
 * @method string errorCode()
 * @method array errorInfo()
 * @method mixed getAttribute($attribute) {}
 */
abstract class DbClient {
    /**
     * @var \PDO
     */
    protected $connection;

    public const MYSQL_DRIVER  = 'mysql';
    public const SQLITE_DRIVER = 'sqlite';

    protected static $pdoConfig = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_STATEMENT_CLASS => [__NAMESPACE__ . '\\Result', []],
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * @param \Morpho\Base\Config|array|\PDO $configOrPdo
     */
    protected function __construct($configOrPdo) {
        if ($configOrPdo instanceof \PDO) {
            self::setPdoAttributes($configOrPdo, static::$pdoConfig);
            $this->connection = $configOrPdo;
        } else {
            $this->connection = $this->newPdo($configOrPdo, $configOrPdo['pdoConfig'] ?? static::$pdoConfig);
        }
    }

    /**
     * @param \Morpho\Base\Config|array|\PDO|null $configOrPdo
     */
    public static function connect($configOrPdo = null): self {
        if (null === $configOrPdo) {
            $configOrPdo = static::defaultConfig();
        }
        if ($configOrPdo instanceof \PDO) {
            $driverName = $configOrPdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } else {
            $driverName = $configOrPdo['driver'];
            unset($configOrPdo['driver']);
        }
        switch ($driverName) {
            case self::MYSQL_DRIVER:
                $db = new MySql\DbClient($configOrPdo);
                break;
            case self::SQLITE_DRIVER:
                $db = new Sqlite\DbClient($configOrPdo);
                break;
            default:
                throw new \UnexpectedValueException();
        }
        return $db;
    }

    public function pdo(): \PDO {
        return $this->connection;
    }

    public static function setPdoAttributes(\PDO $connection, array $pdoConfig): void {
        foreach ($pdoConfig as $name => $value) {
            $connection->setAttribute($name, $value);
        }
    }

    /**
     * @return mixed|false
     */
    public function dbName() {
        return $this->eval($this->query()->dbName())->cell();
    }

    abstract public function query(): GeneralQuery;

    public function newSelectQuery(): SelectQuery {
        return new SelectQuery($this);
    }

    public function newInsertQuery(): InsertQuery {
        return new InsertQuery($this);
    }

    public function newUpdateQuery(): UpdateQuery {
        return new UpdateQuery($this);
    }

    public function newDeleteQuery(): DeleteQuery {
        return new DeleteQuery($this);
    }

    abstract public function newReplaceQuery(): ReplaceQuery;

    // For SELECT use prepare feature.
    public function quote($val, int $type = \PDO::PARAM_STR): string {
        return $this->connection->quote($val, $type);
    }

    public function quoteIdentifier($id): string {
        return $this->query()->quoteIdentifier($id);
    }

    abstract public function schema(): Schema;

    public function select(string $sql, array $args = null): Result {
        return $this->eval('SELECT ' . $sql, $args);
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->connection->lastInsertId($seqName);
    }

    public function insertRow(string $tableName, array $row): void {
        // @TODO: Use InsertQuery
        $query = $this->query();
        $sql = 'INSERT INTO ' . $query->quoteIdentifier($tableName) . '(';
        $sql .= implode(', ', $query->quoteIdentifiers(array_keys($row))) . ') VALUES (' . implode(', ', $query->positionalPlaceholders($row)) . ')';
        $this->eval($sql, array_values($row));
    }

    abstract public function insertRows(string $tableName, array $rows): void;

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): void {
        // @TODO: use DeleteQuery
        $query = $this->query();
        [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
        $sql = 'DELETE FROM ' . $query->quoteIdentifier($tableName)
            . $whereSql;
        /*$stmt = */$this->eval($sql, $whereArgs);
        //return $stmt->rowCount();
    }

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function updateRows(string $tableName, array $row, $whereCondition, array $whereConditionArgs = null): void {
        // @TODO: Use UpdateQuery
        $query = $this->query();
        $sql = 'UPDATE ' . $query->quoteIdentifier($tableName)
            . ' SET ' . implode(', ', $query->namedPlaceholders($row));
        $args = array_values($row);
        if (null !== $whereCondition) {
            [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
            if ($whereSql !== '') {
                $sql .= $whereSql;
                $args = array_merge($args, $whereArgs);
            }
        }
        $this->eval($sql, $args);
    }

    public function eval(string $sql, array $args = null): Result {
        /** @var $stmt Result */
        if ($args) {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($args);
        } else {
            $stmt = $this->connection->query($sql);
        }
        return $stmt;
    }

    public function transaction(callable $transaction) {
        $this->connection->beginTransaction();
        try {
            $result = $transaction($this);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
        return $result;
    }

    public function inTransaction(): bool {
        return $this->connection->inTransaction();
    }

    public function driverName(): string {
        return $this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public function __call(string $method, array $args) {
        return $this->connection->$method(...$args);
    }

    public static function availableDrivers(): array {
        return \PDO::getAvailableDrivers();
    }

    /**
     * See [SQL Syntax Allowed in Prepared Statements](https://dev.mysql.com/doc/refman/5.7/en/sql-syntax-prepared-statements.html#idm139630090954512)
     * @param callable $fn
     * @return mixed
     */
    public function doWithEmulatedPrepares(callable $fn) {
        $emulatePrepares = $this->getAttribute(\PDO::ATTR_EMULATE_PREPARES);
        if (!$emulatePrepares) {
            $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            $result = $fn($this);
            $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $emulatePrepares);
        } else {
            $result = $fn($this);
        }
        return $result;
    }

    abstract protected function newPdo($config, $pdoConfig): \PDO;

    /**
     * @return array|\Morpho\Base\Config
     */
    protected static function defaultConfig() {
        return [
            'driver' => self::MYSQL_DRIVER,
        ];
    }
}
