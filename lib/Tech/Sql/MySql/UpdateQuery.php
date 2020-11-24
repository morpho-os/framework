<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Tech\Sql\IQuery;
use Morpho\Tech\Sql\Result;

class UpdateQuery implements IQuery {
    use TQuery;
    /*
    public function build(): array {
        throw new NotImplementedException();
    }
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    /*
    public function updateRows(string $tableName, array $row, $whereCondition, array $whereConditionArgs = null): void {
        // @TODO: Use UpdateQuery
        $query = $this->query();
        $sql = 'UPDATE ' . $query->quoteIdentifier($tableName)
            . ' SET ' . implode(', ', $query->namedPlaceholders($row));
        $args = array_values($row);
        if (null !== $whereCondition) {
            [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
            if ($whereSql !== '') {
                $sql .= $whereSql;
                $args = array_merge($args, $whereArgs);
            }
        }
        $this->eval($sql, $args);
    }
    */
    public function sql(): string {
        // TODO: Implement sql() method.
    }

    public function args(): array {
        // TODO: Implement args() method.
    }
}