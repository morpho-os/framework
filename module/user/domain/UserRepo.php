<?php
namespace User\Domain;

use Morpho\Base\ArrayTool;
use Morpho\Db\Sql\Repo;
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

    public function saveUser(array $user) {
        $this->insertRow(ArrayTool::getItemsWithKeys($user, ['login', 'passwordHash']));
    }

    public function deleteUser(array $user) {
        $userId = $user['id'];
        if (empty($userId)) {
            throw new \UnexpectedValueException("The User ID must be not empty");
        }
        $this->deleteRows(['id' => $userId]);
    }
}