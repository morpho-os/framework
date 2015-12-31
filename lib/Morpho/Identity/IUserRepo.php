<?php
namespace Morpho\Identity;

interface IUserRepo {
    /**
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserByLogin(string $login);

    public function saveUser(array $user): array;

    public function deleteUser(array $user);

    /**
     * @param string|int $id
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserById($id);
}