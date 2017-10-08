<?php
namespace Morpho\User;

use Morpho\Core\Module as BaseModule;
use Morpho\Identity\UserManager;

class Module extends BaseModule {
    private $initialized = false;

    /**
     * @Listen beforeDispatch -9999
     */
    public function beforeDispatch($event) {
        if (!$this->initialized) {
            $userRepo = $this->repo('User');
            $session = $this->serviceManager->get('session');
            $this->serviceManager->set('userManager', new UserManager($userRepo, $session));
            $this->initialized = true;
        }
    }
}