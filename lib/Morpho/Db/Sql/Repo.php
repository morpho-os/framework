<?php
namespace Morpho\Db\Sql;

use Morpho\Core\Repo as BaseRepo;

class Repo extends BaseRepo {
    protected $tableName;

    protected $pkName = 'id';

    protected static $allowedDbMethods = [
        'lastInsertId',
        'selectRows',
        'selectRow',
        'selectColumn',
        'selectCell',
        'selectMap',
        'transaction',
        'fetchRows',
        'fetchRow',
        'fetchColumn',
        'fetchCell',
        'fetchMap',
        'inTransaction',
    ];

    public function __call(string $method, array $args = []) {
        if (in_array($method, static::$allowedDbMethods, true)) {
            return $this->getDb()->$method(...$args);
        }
        throw new \RuntimeException("Unexpected call");
    }

    public function insertRow(array $row) {
        return $this->getDb()->insertRow($this->tableName, $row);
    }

    public function saveRow(array $row) {
        if (empty($row[$this->pkName])) {
            $this->insertRow($row);
        } else {
            $this->updateRows($row, "{$this->pkName} = ?", [$row[$this->pkName]]);
        }
    }

    public function updateRows(array $row, $whereCondition, $whereConditionArgs = null) {
        $this->getDb()->updateRows($this->tableName, $row, $whereCondition, $whereConditionArgs);
    }

    public function deleteRows($whereCondition, array $whereConditionArgs = null): int {
        return $this->getDb()->deleteRows($this->tableName, $whereCondition, $whereConditionArgs);
    }

    protected function getDb(): Db {
        return $this->serviceManager->get('db');
    }
}