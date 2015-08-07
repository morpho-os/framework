<?php
namespace Morpho\Web;

use Morpho\Db\Db;

class AccessManager {
    protected $session;

    protected $db;

    public function __construct($session, Db $db) {
        $this->session = $session;
        $this->db = $db;
    }

    public function isValidCredentials($login, $pass) {
        return $this->db->selectBool(
            '1 FROM `user` WHERE login = ? AND passHash = ?',
            [$login, $this->hashPassword($pass)]
        );
    }

    public function isLoggedIn() {
        return !empty($this->session->user);
    }

    protected function hashPassword($pass) {
        return password_hash($pass, PASSWORD_DEFAULT);
    }
}