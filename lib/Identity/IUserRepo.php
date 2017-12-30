<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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
    public function userById($id): array;
}