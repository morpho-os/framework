<?php
namespace Morpho\Identity;

use Morpho\Base\EntityExistsException;
use Morpho\Base\NotImplementedException;
use Morpho\Web\Session\Session;

class UserManager {
    protected $session;

    protected $user;

    protected $repo;

    const LOGIN_NOT_FOUND_ERROR      = 'loginNotFound';
    const PASSWORDS_DONT_MATCH_ERROR = 'passwordsDontMatch';
    const EMPTY_LOGIN_OR_PASSWORD    = 'emptyPassword';

    public function __construct(IUserRepo $repo, Session $session) {
        $this->repo = $repo;
        $this->session = $session;
    }

    public function loggedInUser() {
        if (null !== $this->user) {
            return $this->user;
        }
        if (!isset($this->session->userId)) {
            throw new \RuntimeException("The user was not logged in");
        }
        $this->user = $this->registeredUserById($this->session->userId);
        return $this->user;
    }

    public function isUserLoggedIn(): bool {
        return !empty($this->session->userId);
    }

    public function isUserRegistered(array $user): bool {
        return (bool)$this->repo->findUserByLogin($user['login']);
    }

    /**
     * Log in into the system by ID without any checks.
     */
    public function logInById($userId)/*: void */ {
        $registeredUser = $this->registeredUserById($userId);
        $this->session->userId = $registeredUser['id'];
        $this->user = $registeredUser;
    }

    /**
     * @return true|array Returns true on success, array with errors otherwise.
     */
    public function logIn(array $user) {
        $login = trim($user['login']);
        $password = trim($user['password']);

        if (empty($login) || empty($password)) {
            return [self::EMPTY_LOGIN_OR_PASSWORD];
        }

        $registeredUser = $this->repo->findUserByLogin($login);
        if (false === $registeredUser) {
            return [self::LOGIN_NOT_FOUND_ERROR];
        }

        if (!PasswordManager::isValidPassword($password, $registeredUser['passwordHash'])) {
            return [self::PASSWORDS_DONT_MATCH_ERROR];
        }
        unset($registeredUser['passwordHash']);

        $this->session->userId = $registeredUser['id'];
        $this->user = $registeredUser;

        return true;
    }

    public function logOut() {
        $this->user = null;
        unset($this->session->userId);
    }

    /**
     * This operation usually must be done in transaction.
     *
     * @return mixed
     */
    public function registerUser(array $user) {
        if ($this->repo->findUserByLogin($user['login'])) {
            throw new EntityExistsException("Such user already exists");
        }
        $user['passwordHash'] = PasswordManager::passwordHash($user['password']);
        $userId = $this->repo->saveUser($user);
        return $userId;
    }

    public function deleteRegisteredUser(array $user) {
        $this->logOut();
        $this->repo->deleteUser($user);
    }

    public function userInGroup(array $group): bool {
        throw new NotImplementedException();
    }

    private function registeredUserById($id) {
        $registeredUser = $this->repo->userById($id);
        unset($registeredUser['passwordHash']);
        return $registeredUser;
    }
}