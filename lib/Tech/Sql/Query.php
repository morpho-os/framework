<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

abstract class Query implements IQuery {
    /**
     * @var DbClient
     */
    protected $db;

    public function __construct(DbClient $db) {
        $this->db = $db;
    }

    /**
     * Result of this method should not be used for querying database due not proper escaping of values (SQL-injection attack is possible) and may be useful only for debugging.
     */
    public function dump(): string {
        [$sql, $args] = $this->build();
        $notSafeQuote = function ($value) { // @TODO: Move to Query::notSafeQuote()
            if (\preg_match('~^-?\d+$~s', (string)$value)) {
                return \intval($value);
            }
            // @TODO: Handle floats
            // Expression taken from the quoteValue() method, https://github.com/zendframework/zend-db/blob/master/src/Adapter/Platform/AbstractPlatform.php file.
            return '\'' . \addcslashes((string)$value, "\x00\n\r\\'\"\x1a") . '\'';
        };
        foreach ($args as $arg) {
            $sql = \preg_replace('~\?~', $notSafeQuote($arg), $sql, 1);
        }
        return $sql;
    }

    public function eval(): Result {
        [$sql, $args] = $this->build();
        return $this->db->eval($sql, $args);
    }
}
