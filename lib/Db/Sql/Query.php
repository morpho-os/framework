<?php
namespace Morpho\Db\Sql;

abstract class Query {
    public static function logicalAnd(array $expr): string {
        return implode(' AND ', $expr);
    }

    public static function logicalOr(array $expr): string {
        return implode(' OR ', $expr);
    }

    public static function whereClause(string $sql): string {
        return 'WHERE ' . $sql;
    }
    
    public function identifiers(array $identifiers): array {
        $ids = [];
        foreach ($identifiers as $identifier) {
            $ids[] = $this->identifier($identifier);
        }
        return $ids;
    }

    abstract public function identifier(string $identifier): string;

    public function namedPlaceholders(array $row): array {
        $placeholders = [];
        foreach ($row as $key => $value) {
            $placeholders[] = $this->identifier($key) . ' = ?';
        }
        return $placeholders;
    }

    public static function positionalPlaceholders(array $row): array {
        return array_fill(0, count($row), '?');
    }
    
    public static function positionalPlaceholdersString(array $row): string {
        return implode(', ', self::positionalPlaceholders($row));
    }
}