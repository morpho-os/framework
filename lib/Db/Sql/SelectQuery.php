<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql;

/**
 * This class uses some ideas from https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Query/QueryBuilder.php
 */
class SelectQuery extends Query {
    protected const COLUMNS = 'columns';
    protected const FROM = 'from';
    protected const WHERE = 'where';

    protected $parts = [];

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
            $this->parts[self::WHERE] = \array_merge($this->parts[self::WHERE], $where);
        } else {
            $this->parts[self::WHERE] = $where;
        }
        return $this;
    }

    public function build(): array {
        if (isset($this->parts[self::COLUMNS])) {
            if (\is_array($this->parts[self::COLUMNS])) {
                $columnsStr = \implode(', ', $this->parts[self::COLUMNS]);
            } else {
                $columnsStr = $this->parts[self::COLUMNS];
            }
        } else {
            $columnsStr = '*';
        }

        $query = $this->db->query();

        $fromStr = isset($this->parts[self::FROM]) ? $query->quoteIdentifier($this->parts[self::FROM]) : null;

        $whereSql = '';
        $args = [];
        if (isset($this->parts[self::WHERE])) {
            [$whereSql, $args] = $query->whereClause($this->parts[self::WHERE]);
        }

        $sql = 'SELECT ' . $columnsStr
            . (null !== $fromStr ? ' FROM ' . $fromStr : '')
            . $whereSql;

        return [
            $sql,
            $args
        ];
    }
}
