<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\NotImplementedException;
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

    public function eval(): \PDOStatement {
        [$sql, $args] = $this->sqlQueryArgs();
        if ($args) {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($args);
            return $stmt;
        }
        return $this->connection->pdo()->query($sql);
    }

    protected function sqlQueryArgs(): array {
        throw new NotImplementedException();
    }
}