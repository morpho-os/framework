<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Tech\Sql\IQuery;

class DeleteQuery implements IQuery {
    use TQuery;
//    public function build(): array {
//        throw new NotImplementedException();
//    }
/*
    /**
     * @param string $tableName
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): void {
        // @TODO: use DeleteQuery
        $query = $this->query();
        [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
        $sql = 'DELETE FROM ' . $query->quoteIdentifier($tableName) . $whereSql;
        /*$stmt = *$this->eval($sql, $whereArgs);
        //return $stmt->rowCount();
    }
 */
    public function sql(): string {
        // TODO: Implement sql() method.
    }

    public function args(): array {
        // TODO: Implement args() method.
    }
}