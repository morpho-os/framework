<?php
namespace User\Domain;

use Morpho\Db\Repo;
use Morpho\Identity\IUserRepo;

class UserRepo extends Repo implements IUserRepo {
    /**
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserByLogin(string $login) {
        d($login);
    }

    public function saveUser(array $user): array {
        d($user);
    }

    public function deleteUser(array $user) {
        d($user);
    }
}