<?php
namespace Morpho\Db;

use Morpho\Core\Node;

class Repo extends Node {
    protected $tableName;

    protected $pkName = 'id';

    protected function insertRow(array $row) {
        return $this->getDb()->insertRow($this->tableName, $row);
    }
    
    protected function selectRows($sql, array $args = []): array {
        return $this->getDb()->selectRows($sql, $args);
    }

    protected function nameToClass(string $name): string {
        return $name;
    }

    protected function getDb(): Db {
        return $this->serviceManager->get('db');
    }

    protected function getRepo(string $name): Repo {
        return $this->getParentByType('Module')
            ->get($this->getNamespace() . '\\' . $name . REPO_SUFFIX);
    }

    protected function transaction(\Closure $transaction) {
        return $this->getDb()->transaction($transaction);
    }
}
