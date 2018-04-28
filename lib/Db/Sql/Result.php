<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql;

use function Morpho\Base\toArray;

class Result extends \PDOStatement implements \Countable {
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

    /**
     * @return mixed|false Returns false if the value is not found, and other non-false value otherwise.
     */
    public function field() {
        return $this->fetchColumn(0);
    }

    public function boolVal() {
        return (bool) $this->field();
    }

    public function map(): array {
        return $this->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Has time complexity O(n)
     */
    public function count() {
        // @TODO: replace with iterator_count() ?
        $i = 0;
        foreach ($this as $v) {
            $i++;
        }
        return $i;
    }
}