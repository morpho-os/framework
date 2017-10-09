<?php
//declare(strict_types=1);
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\IDumpable;

/**
 * This class uses some ideas from https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Query/QueryBuilder.php
 */
class SelectQuery extends Query implements IDumpable {
    protected $parts = [];

    protected const COLUMNS = 'columns';
    protected const FROM = 'from';
    protected const WHERE = 'where';

    public function from(string $tableName): self {
        $this->parts[self::FROM] = $tableName;
        return $this;
    }

    public function columns($columns): self {
        $this->parts[self::COLUMNS] = $columns;
        return $this;
    }

    public function where(array $where): self {
        if (isset($this->parts[self::WHERE])) {
            $this->parts[self::WHERE] = array_merge($this->parts[self::WHERE], $where);
        } else {
            $this->parts[self::WHERE] = $where;
        }
        return $this;
    }

    /**
     * Result of this method should not be used for querying database due not proper escaping of values (SQL-injection attack is possible) and may be useful only for debugging.
     */
    public function dump(): string {
        $whereSql = '';
        $whereArgs = [];
        if (isset($this->parts[self::WHERE])) {
            [$whereSql, $whereArgs] = $this->whereClause($this->parts[self::WHERE]);
        }
        $quote = function ($value) { // @TODO: Move to Query::notSafeQuote()
            if (preg_match('~^-?\d+$~s', $value)) {
                return intval($value);
            }
            // @TODO: Handle floats
            // Expression taken from the quoteValue() method, https://github.com/zendframework/zend-db/blob/master/src/Adapter/Platform/AbstractPlatform.php file.
            return '\'' . addcslashes((string)$value, "\x00\n\r\\'\"\x1a") . '\'';
        };
        foreach ($whereArgs as $whereArg) {
            $whereSql = preg_replace('~\?~', $quote($whereArg), $whereSql, 1);
        }
        return $this->buildSelect($whereSql);
    }

    protected function sqlQueryArgs(): array {
        $whereSql = '';
        $whereArgs = [];
        if (isset($this->parts[self::WHERE])) {
            [$whereSql, $whereArgs] = $this->whereClause($this->parts[self::WHERE]);
        }
        return [
            $this->buildSelect($whereSql),
            $whereArgs
        ];
    }

    protected function buildSelect(string $whereSql): string {
        if (isset($this->parts[self::COLUMNS])) {
            if (is_array($this->parts[self::COLUMNS])) {
                $columnsStr = implode(', ', $this->parts[self::COLUMNS]);
            } else {
                $columnsStr = $this->parts[self::COLUMNS];
            }
        } else {
            $columnsStr = '*';
        }
        $fromStr = isset($this->parts[self::FROM]) ? $this->identifier($this->parts[self::FROM]) : null;
        return 'SELECT ' . $columnsStr
            . (null !== $fromStr ? ' FROM ' . $fromStr : '')
            . $whereSql;
    }
}