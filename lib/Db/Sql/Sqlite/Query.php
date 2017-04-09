<?php
namespace Morpho\Db\Sql\Sqlite;

use Morpho\Db\Sql\Query as BaseQuery;

class Query extends BaseQuery {
    public function identifier(string $identifier): string {
        return '"' . $identifier . '"';
    }
}