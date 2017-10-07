<?php
//declare(strict_types=1);
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\IDumpable;

/**
 * This class uses some ideas from https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Query/QueryBuilder.php
 */
class SelectQuery extends Query implements IDumpable {
    protected $parts = [];

    protected const FROM = 'from';
    protected const WHERE = 'where';

    public function from(string $tableName): self {
        $this->parts[self::FROM] = $tableName;
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
        [$whereSql, $whereArgs] = $this->whereClause($this->parts[self::WHERE]);
        $quote = function ($value) { // @TODO: Move to Query::notSafeQuote()
            if (preg_match('~^-?\d+$~s', $value)) {
                return intval($value);
            }
            // @TODO: Handle floats
            // Expression taken from the quoteValue() method, https://github.com/zendframework/zend-db/blob/master/src/Adapter/Platform/AbstractPlatform.php file.
            return '\'' . addcslashes((string) $value, "\x00\n\r\\'\"\x1a") . '\'';
        };
        foreach ($whereArgs as $whereArg) {
            $whereSql = preg_replace('~\?~', $quote($whereArg), $whereSql, 1);
        }
        return 'SELECT * FROM ' . $this->identifier($this->parts[self::FROM])
            . $whereSql;
    }
}