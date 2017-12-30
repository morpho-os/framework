<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

use Morpho\Db\Sql\GeneralQuery as BaseGeneralQuery;

class GeneralQuery extends BaseGeneralQuery {
    public static function useDb(string $dbName): string {
        return "USE $dbName";
    }

    /**
     * Returns a query to detect a current database.
     */
    public static function dbName(): string {
        return 'SELECT DATABASE()';
    }

    public function quoteIdentifier(string $identifier): string {
        // @see http://dev.mysql.com/doc/refman/5.7/en/identifiers.html
        return '`' . $identifier . '`';
    }
}