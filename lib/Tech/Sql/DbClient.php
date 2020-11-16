<?php declare(strict_types=1);
namespace Morpho\Tech\Sql;

use PDO;
use Throwable;

/**
 * @method \PDOStatement prepare($statement, array $driver_options = array())
 * @method bool beginTransaction()
 * @method bool commit()
 * @method bool rollBack()
 * @method bool setAttribute($attribute, $value)
 * @method string errorCode()
 * @method array errorInfo()
 * @method mixed getAttribute($attribute) {}
 */
abstract class DbClient implements IDbClient {
    protected PDO $conn;

    protected array $pdoConf = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_STATEMENT_CLASS => [__NAMESPACE__ . '\\Result', []],
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * DbClient constructor.
     * @param PDO|array $confOrPdo
     */
    public function __construct($confOrPdo) {
        $this->conn = $this->connect($confOrPdo);
    }

    public function pdo(): PDO {
        return $this->conn;
    }

    public function insert(): InsertQuery {
        return new InsertQuery($this);
    }

    public function select(): SelectQuery {
        return new SelectQuery($this);
    }

    public function update(): UpdateQuery {
        return new UpdateQuery($this);
    }

    public function delete(): DeleteQuery {
        return new DeleteQuery($this);
    }

    public function replace(): ReplaceQuery {
        return new ReplaceQuery($this);
    }

    public function exec(string $sql): int {
        return $this->conn->exec($sql);
    }

    public function eval(string $sql, array $args = null): Result {
        /** @var $stmt Result */
        if ($args) {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($args);
        } else {
            $stmt = $this->conn->query($sql);
        }
        return $stmt;
    }

    public function lastInsertId(string $name = null): string {
        return $this->conn->lastInsertId($name);
    }

    /**
     * @param callable $transaction
     * @return mixed
     */
    public function transaction(callable $transaction) {
        $this->conn->beginTransaction();
        try {
            $result = $transaction($this);
            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
        return $result;
    }

    public function inTransaction(): bool {
        return $this->conn->inTransaction();
    }

    public function driverName(): string {
        return $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public static function availableDrivers(): array {
        return PDO::getAvailableDrivers();
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args) {
        return $this->conn->$method(...$args);
    }

    /**
     * See [SQL Syntax Allowed in Prepared Statements](https://dev.mysql.com/doc/refman/5.7/en/sql-syntax-prepared-statements.html#idm139630090954512)
     * @param callable $fn
     * @return mixed
     */
    public function withEmulatedPrepares(callable $fn) {
        $emulatePrepares = $this->getAttribute(PDO::ATTR_EMULATE_PREPARES);
        if (!$emulatePrepares) {
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $result = $fn($this);
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulatePrepares);
        } else {
            $result = $fn($this);
        }
        return $result;
    }

    abstract protected function connect($confOrPdo): PDO;

    abstract protected function quoteIdentifier(string $identifier): string;
}