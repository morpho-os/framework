<?php
namespace Morpho\Db\Sql;

use Morpho\Base\DateTime;
use Morpho\Base\EntityNotFoundException;
use Morpho\Core\Repo as BaseRepo;

class Repo extends BaseRepo {
    protected $pkName = 'id';

    protected $tableName;

    protected static $allowedDbMethods = [
        'select',
        'transaction',
    ];

    public function __call(string $method, array $args = []) {
        if (in_array($method, static::$allowedDbMethods, true)) {
            return $this->getDb()->$method(...$args);
        }
        throw new \RuntimeException("Invalid call: " . get_class($this) . "->{$method}(), ensure that this method is properly defined");
    }

    public function inTransaction(): bool {
        return $this->getDb()->inTransaction();
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->getDb()->lastInsertId($seqName);
    }

    public function insertRow(array $row)/*: void*/ {
        $this->getDb()->insertRow($this->tableName, $row);
    }

    public function insertRowAndGetId(array $row, string $seqName = null): string {
        $db = $this->getDb();
        $db->insertRow($this->tableName, $row);
        return $db->lastInsertId($seqName);
    }

    public function saveRow(array $row)/*: void*/ {
        if (empty($row[$this->pkName])) {
            $this->insertRow($row);
        } else {
            $this->updateRows($row, "{$this->pkName} = ?", [$row[$this->pkName]]);
        }
    }

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function updateRows(array $row, $whereCondition, $whereConditionArgs = null)/*: void*/ {
        $this->getDb()->updateRows($this->tableName, $row, $whereCondition, $whereConditionArgs);
    }

    public function deleteRows($whereCondition, array $whereConditionArgs = null): int {
        return $this->getDb()->deleteRows($this->tableName, $whereCondition, $whereConditionArgs);
    }

    protected function getDb(): Db {
        return $this->serviceManager->get('db');
    }

    protected function dateTime(): DateTime {
        return new DateTime();
    }

    protected function dateTimeString(): string {
        return $this->dateTime()->formatDateTime();
    }

    protected function entityNotFoundError(string $message = null)/*: void */ {
        throw new EntityNotFoundException($message);
    }
}