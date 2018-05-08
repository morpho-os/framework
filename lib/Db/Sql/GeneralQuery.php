<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql;

abstract class GeneralQuery {
    abstract public static function useDb(string $dbName): string;

    abstract public static function dbName(): string;

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function whereClause($whereCondition, array $whereConditionArgs = null): array {
        $whereSql = '';
        $whereArgs = [];
        if (\is_array($whereCondition) && \count($whereCondition)) {
            if (null !== $whereConditionArgs) {
                throw new \LogicException('The $whereConditionArgs argument must be empty when the $whereCondition is an array');
            }
            $whereSql .= ' WHERE ' . \implode(' AND ', $this->namedPlaceholders($whereCondition));
            $whereArgs = \array_values($whereCondition);
        } elseif ($whereCondition !== '') {
            // string
            $whereSql .= ' WHERE ' . $whereCondition;
            if (null !== $whereConditionArgs) {
                $whereArgs = $whereConditionArgs;
            }
        }
        return [$whereSql, $whereArgs];
    }

    public function quoteIdentifiers(array $identifiers): array {
        $ids = [];
        foreach ($identifiers as $identifier) {
            $ids[] = $this->quoteIdentifier($identifier);
        }
        return $ids;
    }

    abstract public function quoteIdentifier(string $identifier): string;

    public function namedPlaceholders(array $row): array {
        $placeholders = [];
        foreach ($row as $key => $value) {
            $placeholders[] = $this->quoteIdentifier($key) . ' = ?';
        }
        return $placeholders;
    }

    public static function positionalPlaceholders(array $row): array {
        return \array_fill(0, \count($row), '?');
    }
    
    public static function positionalPlaceholdersStr(array $row): string {
        return \implode(', ', self::positionalPlaceholders($row));
    }

    /*    public static function logicalAnd(array $expr, bool $wrapWithBraces = false): string {
        return \implode(' AND ', $expr);
    }

    public static function logicalOr(array $expr): string {
        return \implode(' OR ', $expr);
    }*/
}
