<?php
namespace Morpho\Identity;

interface IUserRepo {
    /**
     * @return mixed
     */
    public function saveUser(array $user);

    public function deleteUser(array $user);

    /**
     * @return array|false Returns an array with information about User on success, false otherwise.
     */
    public function findUserByLogin(string $login);

    /**
     * @param string|int $id
     * @throws \Morpho\Base\EntityNotFoundException if the User with the given ID is not found.
     */
    public function getUserById($id): array;
}