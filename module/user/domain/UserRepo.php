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

    public function getUserById($id): array {
        return $this->selectRowEx("* FROM $this->tableName WHERE id = ?", [$id]);
    }

    /**
     * @return mixed
     */
    public function saveUser(array $user) {
        $this->insertRow(ArrayTool::itemsWithKeys($user, ['login', 'passwordHash']));
        return $this->lastInsertId('id');
    }

    public function deleteUser(array $user)/*: void */ {
        $userId = $user['id'];
        if (empty($userId)) {
            throw new \UnexpectedValueException("The User ID must be not empty");
        }
        $this->deleteRows(['id' => $userId]);
    }
}