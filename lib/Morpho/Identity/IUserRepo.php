<?php
namespace Morpho\Identity;

interface IUserRepo {
    public function saveUser(array $user);

    public function deleteUser(array $user);

    /**
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserByLogin(string $login);

    /**
     * @param string|int $id
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserById($id);
}