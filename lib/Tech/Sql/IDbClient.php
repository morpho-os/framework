<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

use PDO;

interface IDbClient {
    public function pdo(): PDO;

    public function insert(): InsertQuery;

    public function select(): SelectQuery;

    public function update(): UpdateQuery;

    public function delete(): DeleteQuery;

    public function replace(): ReplaceQuery;

    public function exec(string $sql): int;

    public function eval(string $sql, array $args = null): Result;

    public function lastInsertId(string $name = null): string;

    /**
     * @param callable $transaction
     * @return mixed
     */
    public function transaction(callable $transaction);

    public function inTransaction(): bool;

    public function dbName(): ?string;

    public function useDb(string $dbName): void;

    public function driverName(): string;

    public static function availableDrivers(): array;

    /**
     * See [SQL Syntax Allowed in Prepared Statements](https://dev.mysql.com/doc/refman/5.7/en/sql-syntax-prepared-statements.html#idm139630090954512)
     * @param callable $fn
     * @return mixed
     */
    public function withEmulatedPrepares(callable $fn);
}