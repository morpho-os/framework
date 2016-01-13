<?php
namespace Morpho\Db;

use Morpho\Core\Repo as BaseRepo;

class Repo extends BaseRepo {
    protected $tableName;

    protected $pkName = 'id';

    public function insertRow(array $row) {
        return $this->getDb()->insertRow($this->tableName, $row);
    }

    public function __call(string $method, array $args = []) {
        return $this->getDb()->$method(...$args);
    }

    protected function deleteRows($whereCondition, array $whereConditionArgs = null): int {
        return $this->getDb()->deleteRows($this->tableName, $whereCondition, $whereConditionArgs);
    }

    protected function updateRows(array $row, $whereCondition, $whereConditionArgs = null) {
        $this->getDb()->updateRows($this->tableName, $row, $whereCondition, $whereConditionArgs);
    }

    protected function getDb(): Db {
        return $this->serviceManager->get('db');
    }
}