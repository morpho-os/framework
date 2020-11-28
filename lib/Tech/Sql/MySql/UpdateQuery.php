<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Tech\Sql\IQuery;

class UpdateQuery implements IQuery {
    use TQuery;

    protected array $tables = [];
    protected array $columns = [];

    public function columns($columns): self {
        if (is_array($columns)) {
            $this->columns = array_merge($this->columns, $columns);
        } else {
            $this->columns[] = $columns;
        }
        return $this;
    }

    public function sql(): string {
        /*
                UPDATE [LOW_PRIORITY] [IGNORE] table_reference
                [PARTITION (partition_list)]
          SET col1={expr1|DEFAULT} [,col2={expr2|DEFAULT}] ...
          [WHERE where_condition]

            // todo
          [ORDER BY ...]
          [LIMIT row_count]
        */
        $sql = ['UPDATE'];
        $tableRefSql = $this->tableRefSql();
        if ($tableRefSql) {
            $sql[] = $tableRefSql;
        }
        $sql[] = 'SET';
        $sql[] = implode(', ', $this->db->nameValArgs($this->columns));
        $whereClauseSql = $this->whereClauseSql();
        if (null !== $whereClauseSql) {
            $sql[] = $whereClauseSql;
        }
        return implode("\n", $sql);
    }

    public function args(): array {
        return array_merge(array_values($this->columns), $this->args);
    }
}