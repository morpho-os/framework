<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

use PDO;
use PDOStatement;
use Throwable;
use function implode;

/**
 * @method PDOStatement prepare($statement, array $driver_options = array())
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

    protected string $quote;

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

    public function expr($expr): Expr {
        return new Expr($expr);
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

    public function availableDrivers(): array {
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
     * @param array|string $identifiers
     * @return array|string string
     */
    public function quoteIdentifiers($identifiers) {
        // @see http://dev.mysql.com/doc/refman/5.7/en/identifiers.html
        $quoteIdentifier = function (string $identifiers): string {
            $quoted = [];
            foreach (explode('.', $identifiers) as $identifier) {
                $quoted[] = $this->quote . $identifier . $this->quote;
            }
            return implode('.', $quoted);
        };
        if (!is_array($identifiers)) {
            return $quoteIdentifier($identifiers);
        }
        $ids = [];
        foreach ($identifiers as $identifier) {
            $ids[] = $quoteIdentifier($identifier);
        }
        return $ids;
    }

    public function positionalArgs(array $args): array {
        return array_fill(0, count($args), '?');
    }

    public function nameValArgs(array $args): array {
        $placeholders = [];
        foreach ($args as $name => $val) {
            $placeholders[] = $this->quoteIdentifiers($name) . ' = ?';
        }
        return $placeholders;
    }

    /**
     * See [SQL Syntax Allowed in Prepared Statements](https://dev.mysql.com/doc/refman/5.7/en/sql-syntax-prepared-statements.html#idm139630090954512)
     * @param callable $fn
     * @return mixed
     */
    public function usingEmulatedPrepares(callable $fn) {
        $emulatePrepares = $this->getAttribute(PDO::ATTR_EMULATE_PREPARES);
        if (!$emulatePrepares) {
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            try {
                $result = $fn($this);
            } finally {
                $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulatePrepares);
            }
        } else {
            $result = $fn($this);
        }
        return $result;
    }

    abstract protected function connect($confOrPdo): PDO;
}