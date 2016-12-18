<?php
namespace Morpho\Db\Sql;

class Result extends \PDOStatement {
    // Override the constructor to fix the "PDOException: SQLSTATE[HY000]: General error: user-supplied statement does not accept constructor arguments in ..."
    protected function __construct() {
    }

    public function rows(): array {
        return $this->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array|false
     */
    public function row() {
        return $this->fetch(\PDO::FETCH_ASSOC);
    }

    public function column(): array {
        return $this->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function cell() {
        return $this->fetchColumn(0);
    }

    public function bool() {
        return (bool) $this->cell();
    }

    public function map(): array {
        return $this->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}