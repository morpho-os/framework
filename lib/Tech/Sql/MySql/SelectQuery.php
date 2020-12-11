<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Base\NotImplementedException;
use Morpho\Tech\Sql\Expr;
use Morpho\Tech\Sql\IQuery;

class SelectQuery implements IQuery {
    use TQuery;

    protected array $columns = [];
    protected array $join = [];
    protected array $groupBy = [];
    protected array $having = [];
    //protected array $window = [];
    protected array $orderBy = [];
    protected ?array $limit = null;

    public function columns($columns): self {
        if (is_array($columns)) {
            $this->columns = array_merge($this->columns, $columns);
        } else {
            $this->columns[] = $columns;
        }
        return $this;
    }

    public function sql(): string {
        $sql = [];

        $columns = [];
        if ($this->columns) {
            foreach ($this->columns as $column) {
                if (is_array($column)) {
                    $columns = array_merge($columns, $this->db->quoteIdentifier($column));
                } elseif ($column === '*') {
                    $columns[] = $column;
                } else {
                    $columns[] = $column instanceof Expr
                        ? $column->val()
                        : $this->db->quoteIdentifier($column);
                }
            }
        }
        $hasFrom = count($this->tables);
        if ($columns) {
            $sql[] = 'SELECT ' . implode(', ', $columns);
        } elseif ($hasFrom || $this->where) {
            $sql[] = 'SELECT *';
        }

        if ($hasFrom) {
            $sql[] = 'FROM ' . $this->tableRefSql();
        }
        foreach ($this->join as $join) {
            $sql[] = $join[0] . ' JOIN ' . $join[1];
        }
        $whereClauseSql = $this->whereClauseSql();
        if (null !== $whereClauseSql) {
            $sql[] = $whereClauseSql;
        }

        if ($this->groupBy) {
            $sql[] = 'GROUP BY ' . $this->db->quoteIdentifierStr($this->groupBy);
        }

        if ($this->having) {
            $sql[] = 'HAVING ' . implode(' AND ', $this->having);
        }

        if ($this->orderBy) {
            $sql[] = 'ORDER BY ' . $this->db->quoteIdentifierStr($this->orderBy);
        }

        if ($this->limit) {
            [$offset, $numOfRows] = $this->limit;
            $sql[] = 'LIMIT '
                    . (null !== $offset ? intval($offset) . ', ' : '')
                    . intval($numOfRows);
        }

        return implode("\n", $sql);
    }

    public function into() {
        throw new NotImplementedException();
    }

    public function union() {
        // https://dev.mysql.com/doc/refman/8.0/en/union.html
        throw new NotImplementedException();
    }

    public function leftJoin(string $join): self {
        $this->join[] = ['LEFT', $join];
        return $this;
    }

    public function innerJoin($join): self {
        $this->join[] = ['INNER', $join];
        return $this;
    }

    public function rightJoin(string $join): self {
        $this->join[] = ['RIGHT', $join];
        return $this;
    }

    public function groupBy($sql): self {
        $this->groupBy[] = $sql;
        return $this;
    }

    public function having($sql): self {
        $this->having[] = $sql;
        return $this;
    }

    public function orderBy($sql): self {
        $this->orderBy[] = $sql;
        return $this;
    }

    public function limit(int $numOfRows, int $offset = null): self {
        $this->limit = [$offset, $numOfRows];
        return $this;
    }
}