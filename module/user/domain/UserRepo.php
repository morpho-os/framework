<?php
namespace User\Domain;

use Morpho\Db\Repo;
use Morpho\Identity\IUserRepo;

class UserRepo extends Repo implements IUserRepo {
    protected $tableName = 'user';

    /**
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserByLogin(string $login) {
        return $this->getDb()->selectRow("* FROM $this->tableName WHERE login = ?", [$login]);
    }

    /**
     * @param string|int $id
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserById($id) {
        return $this->getDb()->selectRow("* FROM $this->tableName WHERE id = ?", [$id]);
    }

    public function saveUser(array $user): array {
        d($user);
    }

    public function deleteUser(array $user) {
        d($user);
    }
}