<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Tech\Sql\Expr;
use Morpho\Tech\Sql\IQuery;
use UnexpectedValueException;

class SelectQuery implements IQuery {
    use TQuery;

    protected array $columns = [];
    protected array $from = [];
    protected array $where = [];
    protected array $groupBy = [];
    protected array $having = [];
    protected array $window = [];
    protected array $orderBy = [];
    protected array $limit = [];
    protected array $offset = [];

    protected array $args = [];

    public function columns($columns): self {
        $this->columns[] = $columns;
        return $this;
    }

    public function from($tableReference): self {
        $this->from[] = $tableReference;
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
        $hasFrom = count($this->from);
        if ($columns) {
            $sql[] = implode(', ', $columns);
        } elseif ($hasFrom || $this->where) {
            $sql[] = '*';
        }

        if ($hasFrom) {
            $sql[] = 'FROM';
            foreach ($this->from as $from) {
                $sql[] = $from instanceof Expr ? $from->val() : $this->db->quoteIdentifier($from);
            }
        }
        if ($this->where) {
            $sql[] = 'WHERE ' . implode(' AND ', $this->where);
        }
        /*

                                      SELECT
                                      select_options
                                      select_item_list
                                      into_clause
                                      from
                                      where
                                      group
                                      having
                                      windows


        SELECT
            [ALL | DISTINCT | DISTINCTROW ]
            [HIGH_PRIORITY]
            [STRAIGHT_JOIN]
            [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
            [SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
            select_expr [, select_expr] ...
            [into_option]
               [PARTITION partition_list]]
            [WHERE where_condition]
            [GROUP BY {col_name | expr | position}, ... [WITH ROLLUP]]
            [HAVING where_condition]
            [WINDOW window_name AS (window_spec)
                [, window_name AS (window_spec)] ...]
            [ORDER BY {col_name | expr | position}
              [ASC | DESC], ... [WITH ROLLUP]]
            [LIMIT {[offset,] row_count | row_count OFFSET offset}]
            [into_option]
            [FOR {UPDATE | SHARE}
                [OF tbl_name [, tbl_name] ...]
                [NOWAIT | SKIP LOCKED]
              | LOCK IN SHARE MODE]
            [into_option]

        into_option: {
            INTO OUTFILE 'file_name'
                [CHARACTER SET charset_name]
                export_options
          | INTO DUMPFILE 'file_name'
          | INTO var_name [, var_name] ...
        }
         */
        return implode("\n", $sql);
    }

    public function args(): array {
        return $this->args;
    }

    public function where($condition, array $args = null): self {
        if (null === $args) {
            if (!is_array($condition)) {
                throw new UnexpectedValueException();
            }
            // If args are not specified then $condition contains arguments
            $this->where[] = implode(' AND ', $this->db->nameValArgs($condition));
            $this->args = array_merge($this->args, array_values($condition));
        } else {
            $this->where[] = $condition;
            $this->args = array_merge($this->args, $args);
        }
        return $this;
    }
        /*

    public function innerJoin($table) {
table_references:
    escaped_table_reference [, escaped_table_reference] ...

escaped_table_reference: {
    table_reference
  | { OJ table_reference }
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
     */
}