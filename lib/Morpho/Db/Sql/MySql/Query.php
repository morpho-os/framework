<?php
namespace Morpho\Db\Sql\MySql;

use Morpho\Db\Sql\Query as BaseQuery;

class Query extends BaseQuery {
    public static function useDb(string $dbName): string {
        return "USE $dbName";
    }

    public static function currentDb(): string {
        return 'SELECT DATABASE()';
    }

    public function identifier(string $identifier): string {
        // @see http://dev.mysql.com/doc/refman/5.7/en/identifiers.html
        return '`' . $identifier . '`';
    }
}