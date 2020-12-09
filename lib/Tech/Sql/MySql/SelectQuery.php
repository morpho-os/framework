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
    protected array $window = [];
    protected array $orderBy = [];
    protected array $limit = [];
    protected array $offset = [];

    public function columns($columns): self {
        if (is_array($columns)) {
            $this->columns = array_merge($this->columns, $columns);
        } else {
            $this->columns[] = $columns;
        }
        return $this;
    }

    public function sql(): string {
        $sql = ['SELECT'];

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
            $sql[] = implode(', ', $columns);
        } elseif ($hasFrom || $this->where) {
            $sql[] = '*';
        }

        if ($hasFrom) {
            $sql[] = 'FROM';
            $sql[] = $this->tableRefSql();
        }
        foreach ($this->join as $join) {
            $sql[] = $join[0] . ' JOIN ' . $join[1];
        }
        $whereClauseSql = $this->whereClauseSql();
        if (null !== $whereClauseSql) {
            $sql[] = $whereClauseSql;
        }
/*

SELECT
    [ALL | DISTINCT | DISTINCTROW ]
    [HIGH_PRIORITY]
    [STRAIGHT_JOIN]
    [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
    [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
    select_expr [, select_expr] ...
    [into_option]
    [FROM table_references
      [PARTITION partition_list]]
    [WHERE where_condition]
    [GROUP BY {col_name | expr | position}
      [ASC | DESC], ... [WITH ROLLUP]]
    [HAVING where_condition]
    [ORDER BY {col_name | expr | position}
      [ASC | DESC], ...]
    [LIMIT {[offset,] row_count | row_count OFFSET offset}]
    [PROCEDURE procedure_name(argument_list)]
    [into_option]
    [FOR UPDATE | LOCK IN SHARE MODE]

into_option: {
    INTO OUTFILE 'file_name'
        [CHARACTER SET charset_name]
        export_options
  | INTO DUMPFILE 'file_name'
  | INTO var_name [, var_name] ...
}




table_reference: {
    table_factor
  | joined_table
}

table_factor: {
    tbl_name [PARTITION (partition_names)]
        [[AS] alias] [index_hint_list]
  | [LATERAL] table_subquery [AS] alias [(col_list)]
  | ( table_references )
}

joined_table: {
    table_reference {[INNER | CROSS] JOIN | STRAIGHT_JOIN} table_factor [join_specification]
  | table_reference {LEFT|RIGHT} [OUTER] JOIN table_reference join_specification
  | table_reference NATURAL [INNER | {LEFT|RIGHT} [OUTER]] JOIN table_factor
}

join_specification: {
    ON search_condition
  | USING (join_column_list)
}

join_column_list:
    column_name [, column_name] ...

index_hint_list:
    index_hint [, index_hint] ...

index_hint: {
    USE {INDEX|KEY}
      [FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
  | {IGNORE|FORCE} {INDEX|KEY}
      [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
}

index_list:
    index_name [, index_name] ...

    }

SELECT
select_options
select_item_list
into_clause
from table_reference
where
group
having
windows
*/
        return implode("\n", $sql);
    }

    public function into() {
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

    public function groupBy() {
        throw new NotImplementedException();
    }

    public function having() {
        throw new NotImplementedException();
    }

    public function orderBy() {
        throw new NotImplementedException();
    }

    public function limit() {
        throw new NotImplementedException();
    }
}