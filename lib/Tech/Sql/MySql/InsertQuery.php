<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Base\NotImplementedException;
use Morpho\Tech\Sql\IQuery;

class InsertQuery implements IQuery {
    use TQuery;

    protected string $tableName;

    protected array $rows = [];

    public function into(string $tableName): self {
        $this->tableName = $tableName;
        return $this;
    }

    public function row(array $row): self {
        $this->rows[] = $row;
        //$args = \array_values($row);
        //return [$sql, $args];
        return $this;
    }

    public function args(): array {
        $args = [];
        foreach ($this->rows as $row) {
            $args = array_merge($args, array_values($row));
        }
        return $args;
    }

    /**
     * @return string
     */
    public function lastId() {
        return $this->db->lastInsertId();
    }

    // todo: public funciton rows(array $rows): self {
    //}

/*
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

    abstract public function insertRows(string $tableName, array $rows): void;
*/
    public function sql(): string {
        if (count($this->rows) > 1) {
            throw new NotImplementedException();
        }
        $row = $this->rows[0]; // todo: support multiple rows
        return 'INSERT INTO ' . $this->db->quoteIdentifier($this->tableName)
            . ' (' . \implode(', ', $this->db->quoteIdentifier(\array_keys($row))) . ') VALUES (' . \implode(', ', $this->db->positionalArgs($row)) . ')';
    }
}
