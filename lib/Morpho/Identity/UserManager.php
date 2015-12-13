<?php
namespace Morpho\Identity;

use Morpho\Db\Db;
use Morpho\Web\Session;

class UserManager {
    protected $db;

    protected $session;

    protected $user;

    public function __construct(Db $db, Session $session) {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * @return false|array Returns false for the guest and array for the authenticated user.
     */
    public function getUser() {
        if (null !== $this->user) {
            return $this->user;
        }
        if (empty($this->session->userId)) {
            $this->user = false;
        } else {
            $this->user = $this->db->selectRow('id, login FROM `user` WHERE id = ?', [$this->session->userId]);
        }
        return $this->user;
    }

    public function isGuestUser(): bool {
        return empty($this->session->userId);
    }

    public function logIn($login, $pass) {
        $userId = $this->authenticate($login, $pass);
        if (false !== $userId) {
            $this->session->userId = $userId;
        }
        return $userId;
    }

    public function logOut() {
        unset($this->session->userId);
    }

    /**
     * @param string $login
     * @param string $password
     * @return false|string Returns User ID on success, false on failure.
     */
    public function authenticate($login, $password) {
        $row = $this->db->selectRow('id, passwordHash FROM `user` WHERE login = ?', [$login]);
        if ($row) {
            if (PasswordManager::isValidPassword($password, $row['passwordHash'])) {
                return $row['id'];
            }
        }
        return false;
    }
}