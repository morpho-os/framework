<?php declare(strict_types=1);
namespace Morpho\User\Domain;

use Morpho\Base\Arr;
use Morpho\Db\Sql\Repo;
use Morpho\Identity\IUserRepo;

class UserRepo extends Repo implements IUserRepo {
    protected $tableName = 'user';

    /**
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserByLogin(string $login) {
        return $this->db()->select("* FROM $this->tableName WHERE login = ?", [$login])->row();
    }

    public function userById($id): array {
        return $this->selectRowEx("* FROM $this->tableName WHERE id = ?", [$id]);
    }

    /**
     * @return mixed
     */
    public function saveUser(array $user) {
        $this->insertRow(Arr::itemsWithKeys($user, ['login', 'passwordHash']));
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