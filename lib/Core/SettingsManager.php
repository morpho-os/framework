<?php
namespace Morpho\Core;

use Morpho\Base\NotImplementedException;
use Morpho\Db\Sql\Db;

class SettingsManager {
    protected $db;

    private $cache = [];

    const TABLE_NAME = 'setting';

    public function __construct(Db $db) {
        $this->db = $db;
    }

    public function setDb(Db $db) {
        $this->db = $db;
    }

    public function delete() {
        throw new NotImplementedException();
        unset($this->cache[$name]);
    }

    /**
     * @param string $moduleName
     * @return mixed Returns non false value if setting with $name exists, false otherwise.
     */
    public function get(string $name, $moduleName) {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        $value = $this->db->select(
            'value FROM ' . self::TABLE_NAME . ' AS s
                INNER JOIN module AS m
             ON s.moduleId = m.id
             WHERE s.name = ? AND m.name = ?',
            [$name, $moduleName]
        )->cell();
        return unserialize($value);
    }

    public function set(string $name, $value, $moduleName) {
        if (empty($name)) {
            throw new \UnexpectedValueException("Empty setting name");
        }
        if (empty($moduleName)) {
            throw new \UnexpectedValueException("Empty module name");
        }
        $row = $this->db->select(
            's.name, m.id AS moduleId
            FROM `module` AS m
            LEFT JOIN ' . self::TABLE_NAME . ' AS s
                ON s.moduleId = m.id AND s.name = ?
            WHERE m.name = ?',
            [$name, $moduleName]
        )->row();
        if (!$row) {
            throw new \LogicException("Unable to select module ID");
        }
        $moduleId = $row['moduleId'];
        if (null !== $row['name']) {
            $this->db->updateRows(
                self::TABLE_NAME,
                ['value' => serialize($value)],
                ['name' => $name, 'moduleId' => $moduleId]
            );
        } else {
            $this->db->insertRow(
                self::TABLE_NAME,
                ['name' => $name, 'value' => serialize($value), 'moduleId' => $moduleId]
            );
        }
        $this->cache[$name] = $value;
    }
}
