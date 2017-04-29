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
            return $this->db()->$method(...$args);
        }
        throw new \RuntimeException("Invalid call: " . get_class($this) . "->{$method}(), ensure that this method is properly defined");
    }

    public function inTransaction(): bool {
        return $this->db()->inTransaction();
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->db()->lastInsertId($seqName);
    }

    public function insertRow(array $row, string $idColumnName = null) {
        $db = $this->db();
        $db->insertRow($this->tableName, $row);
        if ($idColumnName) {
            return $db->lastInsertId($idColumnName);
        }
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
        $this->db()->updateRows($this->tableName, $row, $whereCondition, $whereConditionArgs);
    }

    public function deleteRows($whereCondition, array $whereConditionArgs = null): int {
        return $this->db()->deleteRows($this->tableName, $whereCondition, $whereConditionArgs);
    }

    protected function db(): Db {
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