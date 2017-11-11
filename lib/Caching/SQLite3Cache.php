<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

use SQLite3;
use SQLite3Result;

/**
 * This class based on \Doctrine\Common\Cache\SQLite3Cache from Doctrine project
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Jake Bell <jake@theunraveler.com>
 */
class SQLite3Cache extends Cache {
    /**
     * The ID field will store the cache key.
     */
    const ID_FIELD = 'k';

    /**
     * The data field will store the serialized PHP value.
     */
    const DATA_FIELD = 'd';

    /**
     * The expiration field will store a date value indicating when the
     * cache entry should expire.
     */
    const EXPIRATION_FIELD = 'e';

    /**
     * @var SQLite3
     */
    private $sqlite;

    /**
     * @var string
     */
    private $table;

    /**
     * Constructor.
     *
     * Calling the constructor will ensure that the database file and table
     * exist and will create both if they don't.
     *
     * @param SQLite3 $sqlite
     * @param string $table
     */
    public function __construct(SQLite3 $sqlite, $table) {
        $this->sqlite = $sqlite;
        $this->table = (string)$table;

        $this->ensureTableExists();
    }

    public function stats(): ?array {
        return null;
    }

    public function delete($key) {
        list($idField) = $this->getFields();

        $statement = $this->sqlite->prepare(sprintf(
            'DELETE FROM %s WHERE %s = :id',
            $this->table,
            $idField
        ));

        $statement->bindValue(':id', $key);

        return $statement->execute() instanceof SQLite3Result;
    }

    public function clear(): bool {
        return $this->sqlite->exec(sprintf('DELETE FROM %s', $this->table));
    }

    protected function contains(string $key): bool {
        return null !== $this->findById($key, false);
    }

    protected function save(string $key, $data, $lifeTime = 0): bool {
        $statement = $this->sqlite->prepare(sprintf(
            'INSERT OR REPLACE INTO %s (%s) VALUES (:id, :data, :expire)',
            $this->table,
            implode(',', $this->getFields())
        ));

        $statement->bindValue(':id', $key);
        $statement->bindValue(':data', serialize($data), SQLITE3_BLOB);
        $statement->bindValue(':expire', $lifeTime > 0 ? time() + $lifeTime : null);

        return $statement->execute() instanceof SQLite3Result;
    }

    protected function fetch(string $key) {
        $item = $this->findById($key);
        if (!$item) {
            return false;
        }
        return unserialize($item[self::DATA_FIELD]);
    }

    private function ensureTableExists(): void {
        $this->sqlite->exec(
            sprintf(
                'CREATE TABLE IF NOT EXISTS %s(%s TEXT PRIMARY KEY NOT NULL, %s BLOB, %s INTEGER)',
                $this->table,
                static::ID_FIELD,
                static::DATA_FIELD,
                static::EXPIRATION_FIELD
            )
        );
    }

    /**
     * Find a single row by ID.
     *
     * @param mixed $key
     * @param bool $includeData
     *
     * @return array|null
     */
    private function findById($key, bool $includeData = true): ?array {
        list($idField) = $fields = $this->getFields();

        if (!$includeData) {
            $key = array_search(static::DATA_FIELD, $fields);
            unset($fields[$key]);
        }

        $statement = $this->sqlite->prepare(sprintf(
            'SELECT %s FROM %s WHERE %s = :id LIMIT 1',
            implode(',', $fields),
            $this->table,
            $idField
        ));

        $statement->bindValue(':id', $key, SQLITE3_TEXT);

        $item = $statement->execute()->fetchArray(SQLITE3_ASSOC);

        if ($item === false) {
            return null;
        }

        if ($this->isExpired($item)) {
            $this->delete($key);

            return null;
        }

        return $item;
    }

    /**
     * Gets an array of the fields in our table.
     *
     * @return array
     */
    private function getFields(): array {
        return [static::ID_FIELD, static::DATA_FIELD, static::EXPIRATION_FIELD];
    }

    /**
     * Check if the item is expired.
     *
     * @param array $item
     *
     * @return bool
     */
    private function isExpired(array $item): bool {
        return isset($item[static::EXPIRATION_FIELD]) &&
            $item[self::EXPIRATION_FIELD] !== null &&
            $item[self::EXPIRATION_FIELD] < time();
    }
}
