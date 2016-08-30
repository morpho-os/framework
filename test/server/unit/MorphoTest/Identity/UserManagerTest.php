<?php
namespace MorphoTest\Identity;

use Morpho\Base\EntityNotFoundException;
use Morpho\Identity\IUserRepo;
use Morpho\Identity\UserManager;
use Morpho\Web\Session;
use Morpho\Test\DbTestCase;

class UserManagerTest extends DbTestCase {
    public function setUp() {
        parent::setUp();

        $userRepo = new class implements IUserRepo {
            private $id = 0;

            private $users = [];

            /**
             * @return array|false
             */
            public function findUserByLogin(string $login) {
                return isset($this->users[$login]) ? $this->users[$login] : false;
            }

            public function saveUser(array $user) {
                $user['id'] = ++$this->id;
                $this->users[$user['login']] = $user;
                return $user;
            }

            public function deleteUser(array $user) {
                unset($this->users[$user['login']]);
            }
            
            public function getUserById($id): array {
                foreach ($this->users as $user) {
                    if ($user['id'] === $id) {
                        return $user;
                    }
                }
                throw new EntityNotFoundException();
            }
        };
        $_SESSION = [];
        $this->userManager = new UserManager($userRepo, new Session(__CLASS__));
    }

    public function testRegistration() {
        $user = ['login' => 'foo', 'password' => 'bar'];

        $this->assertGetLoggedInUserThrowsUserNotLoggedInException();
        $this->assertFalse($this->userManager->isUserRegistered($user));
        $this->assertFalse($this->userManager->isUserLoggedIn($user));

        $this->userManager->registerUser($user);

        $this->assertTrue($this->userManager->isUserRegistered($user));
        $this->assertFalse($this->userManager->isUserLoggedIn($user));
        $this->assertGetLoggedInUserThrowsUserNotLoggedInException();

        $this->assertTrue($this->userManager->logIn($user));

        $this->assertTrue($this->userManager->isUserRegistered($user));
        $this->assertTrue($this->userManager->isUserLoggedIn());
        $loggedInUser = $this->userManager->getLoggedInUser();
        $this->assertEquals($user['login'], $loggedInUser['login']);
        $this->assertEquals($user['password'], $loggedInUser['password']);
        $this->assertNotEmpty($loggedInUser['id']);
        $this->assertEquals($loggedInUser, $this->userManager->getLoggedInUser());

        $this->assertNull($this->userManager->logOut());

        $this->assertTrue($this->userManager->isUserRegistered($user));
        $this->assertFalse($this->userManager->isUserLoggedIn());
        $this->assertGetLoggedInUserThrowsUserNotLoggedInException();

        $this->userManager->deleteRegisteredUser($user);

        $this->assertFalse($this->userManager->isUserRegistered($user));
        $this->assertFalse($this->userManager->isUserLoggedIn());
        $this->assertGetLoggedInUserThrowsUserNotLoggedInException();
    }

    public function testRegister_TwiceThrowsException() {
        $user = ['login' => 'foo', 'password' => 'bar'];
        $this->userManager->registerUser($user);
        $this->expectException('\Morpho\Base\EntityExistsException', 'Such user already exists');
        $this->userManager->registerUser($user);
    }

    public function testLogIn_TwiceWorks() {
        $user = ['login' => 'I', 'password' => 'pass'];
        $this->userManager->registerUser($user);
        $this->assertTrue($this->userManager->logIn($user));
        $this->assertTrue($this->userManager->logIn($user));
    }

    public function testLogIn_NotRegisteredReturnsError() {
        $user = ['login' => 'foo', 'password' => 'bar'];
        $this->assertEquals([UserManager::LOGIN_NOT_FOUND_ERROR], $this->userManager->logIn($user));
    }

    public function testLogIn_InvalidPasswordReturnsError() {
        $user = ['login' => 'foo', 'password' => 'bar'];
        $this->userManager->registerUser($user);
        $user['password'] = 'invalid';
        $this->assertEquals([UserManager::PASSWORDS_DONT_MATCH_ERROR], $this->userManager->logIn($user));
    }

    public function dataForLogIn_EmptyLoginOrPasswordReturnsError() {
        return [
            [
                'my-login',
                '',
            ],
            [
                '',
                'my-password',
            ],
            [
                '',
                '',
            ],
        ];
    }

    /**
     * @dataProvider dataForLogIn_EmptyLoginOrPasswordReturnsError
     */
    public function testLogIn_EmptyLoginOrPasswordReturnsError($login, $password) {
        $this->userManager->registerUser(['login' => $login, 'password' => $password]);
        $this->assertEquals([UserManager::EMPTY_LOGIN_OR_PASSWORD], $this->userManager->logIn(['login' => $login, 'password' => $password]));
    }

    private function assertGetLoggedInUserThrowsUserNotLoggedInException() {
        try {
            $this->userManager->getLoggedInUser();
            $this->fail();
        } catch (\RuntimeException $e) {
            $this->assertEquals('The user was not logged in', $e->getMessage());
        }
    }
}