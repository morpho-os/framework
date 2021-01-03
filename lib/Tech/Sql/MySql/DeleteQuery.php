<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

class DeleteQuery extends Query {
//    public function build(): array {
//        throw new NotImplementedException();
//    }

/*
    /**
     * @param string $tableName
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): void {

    }
 */
    public function sql(): string {
        $sql = ['DELETE', 'FROM', $this->db->quoteIdentifierStr($this->tables)];
/*
DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
    tbl_name[.*] [, tbl_name[.*]] ...
    FROM table_references
    [WHERE where_condition]

Or:

DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
    FROM tbl_name[.*] [, tbl_name[.*]] ...
    USING table_references
    [WHERE where_condition]

 */
        if ($this->where) {
            $sql[] = $this->whereStr();
        }
        return implode("\n", $sql);
    }
}