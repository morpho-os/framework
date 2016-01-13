<?php
namespace Morpho\Identity;

use Morpho\Base\EntityExistsException;
use Morpho\Base\EntityNotFoundException;
use Morpho\Base\NotImplementedException;
use Morpho\Web\Session;

class UserManager {
    protected $session;

    protected $user;

    protected $repo;

    const USER_NOT_FOUND_ERROR = 'userNotFound';
    const LOGIN_NOT_FOUND_ERROR = 'loginNotFound';
    const PASSWORDS_DONT_MATCH_ERROR = 'passwordsDontMatch';

    public function __construct(IUserRepo $repo, Session $session) {
        $this->repo = $repo;
        $this->session = $session;
    }

    public function getLoggedInUser() {
        if (null !== $this->user) {
            return $this->user;
        }
        if (!isset($this->session->userId)) {
            throw new \RuntimeException("The user was not logged in");
        }
        $this->user = $this->repo->findUserById($this->session->userId);
        if (false === $this->user) {
            throw new EntityNotFoundException("The user with ID {$this->session->userId} does not exist");
        }
        return $this->user;
    }

    public function isUserLoggedIn(): bool {
        return !empty($this->session->userId);
    }

    public function isUserRegistered(array $user): bool {
        return (bool)$this->repo->findUserByLogin($user['login']);
    }

    /**
     * @return true|array Returns true on success, array with errors otherwise.
     */
    public function logIn(array $user) {
        $registeredUser = $this->repo->findUserByLogin($user['login']);
        if (false === $registeredUser) {
            return [self::USER_NOT_FOUND_ERROR, self::LOGIN_NOT_FOUND_ERROR];
        }
        if (!PasswordManager::isValidPassword($user['password'], $registeredUser['passwordHash'])) {
            return [self::USER_NOT_FOUND_ERROR, self::PASSWORDS_DONT_MATCH_ERROR];
        }
        $this->session->userId = $registeredUser['id'];
        $this->user = $registeredUser;
        return true;
    }

    public function logOut() {
        $this->user = null;
        unset($this->session->userId);
    }

    public function registerUser(array $user) {
        if ($this->repo->findUserByLogin($user['login'])) {
            throw new EntityExistsException("Such user already exists");
        }
        $user['passwordHash'] = PasswordManager::passwordHash($user['password']);
        $this->repo->saveUser($user);
    }

    public function deleteRegisteredUser(array $user) {
        $this->logOut();
        $this->repo->deleteUser($user);
    }

    public function userHasRole(array $role): bool {
        throw new NotImplementedException();
    }
}