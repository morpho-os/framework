<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

class InsertQuery extends Query {
    protected const TABLE = 'table';
    protected const ROW = 'row';

    protected $parts = [];

    public function table(string $tableName): self {
        $this->parts[self::TABLE] = $tableName;
        return $this;
    }

    public function row(array $row): self {
        $this->parts[self::ROW] = $row;
        return $this;
    }

    public function build(): array {
        $query = $this->db->query();
        $row = $this->parts[self::ROW];
        $sql = 'INSERT INTO ' . $query->quoteIdentifier($this->parts[self::TABLE])
            . ' (' . \implode(', ', $query->quoteIdentifiers(\array_keys($row))) . ') VALUES (' . \implode(', ', $query->positionalPlaceholders($row)) . ')';
        $args = \array_values($row);
        return [$sql, $args];
    }
}
