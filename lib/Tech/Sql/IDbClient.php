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

    public function expr($expr): Expr;

    public function insert($spec = null): IQuery;

    public function select($spec = null): IQuery;

    public function update($spec = null): IQuery;

    public function delete($spec = null): IQuery;

    public function replace($spec = null): IQuery;

    public function exec(string $sql): int;

    public function eval(string $sql, array $args = null): Result;

    public function lastInsertId(string $name = null): string;

    /**
     * @param callable $transaction
     * @return mixed
     */
    public function transaction(callable $transaction);

    public function inTransaction(): bool;

    public function schema(): ISchema;

    /**
     * @return string|null
     */
    public function dbName(): ?string;

    public function useDb(string $dbName): self;

    public function driverName(): string;

    public function availableDrivers(): array;

    /**
     * @param string|array $identifiers
     * @return string|array
     */
    public function quoteIdentifier($identifiers);

    /**
     * Returns SQL-query for quoted identifiers. If an array has been provided, then separates them with comma.
     */
    public function quoteIdentifierStr($identifiers): string;

    public function positionalArgs(array $args): array;

    // [':foo = ?', ':bar = ?']
    //public function namedArgs(array $args): array;

    public function nameValArgs(array $args): array;
/*
    public function commaSep(array $exprs): string;

    public function logicalOr(array $exprs): string;

    public function logicalAnd(array $exprs): string;
*/
    /**
     * See [SQL Syntax Allowed in Prepared Statements](https://dev.mysql.com/doc/refman/5.7/en/sql-syntax-prepared-statements.html#idm139630090954512)
     * @param callable $fn
     * @return mixed
     */
    public function usingEmulatedPrepares(callable $fn);
}