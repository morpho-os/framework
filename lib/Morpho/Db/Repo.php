<?php
namespace Morpho\Db;

use Morpho\Core\Repo as BaseRepo;

class Repo extends BaseRepo {
    protected $tableName;

    protected $pkName = 'id';

    protected function insertRow(array $row) {
        return $this->getDb()->insertRow($this->tableName, $row);
    }

    protected function selectBool(string $sql, array $args = []): bool {
        return $this->getDb()->selectBool($sql, $args);
    }
    
    protected function selectRows(string $sql, array $args = []): array {
        return $this->getDb()->selectRows($sql, $args);
    }

    protected function getDb(): Db {
        return $this->serviceManager->get('db');
    }

    protected function transaction(\Closure $transaction) {
        return $this->getDb()->transaction($transaction);
    }
}