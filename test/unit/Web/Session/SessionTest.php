<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web\Session;

use Morpho\Test\TestCase;
use Morpho\Web\Session\Session;

class SessionTest extends TestCase {
    public function setUp() {
        $_SESSION = [];
        $this->session = new Session(__CLASS__, false);
    }

    public function testStorageOfClosure() {
        $uniqId = uniqid();
        $closure = function () use ($uniqId) {
            return $uniqId;
        };
        $this->session->fn = $closure;

        $this->assertTrue(isset($this->session->fn));

        unset($this->session);

        $session = new Session(__CLASS__, false);

        $fn = $session->fn;
        $this->assertEquals($uniqId, $fn());
    }

    public function testImplementsInterfaces() {
        $this->assertInstanceOf('\Countable', $this->session);
        $this->assertInstanceOf('\Iterator', $this->session);
        $this->assertInstanceOf('\ArrayAccess', $this->session);
    }

    public function testStorageKey() {
        $this->assertEquals(__CLASS__, $this->session->storageKey());
    }

    public function testMagicMethods() {
        $this->assertTrue(empty($_SESSION[Session::KEY][__CLASS__]));
        $this->assertEquals(0, count($this->session));
        $this->assertFalse(isset($this->session->foo));
        $this->assertTrue(empty($this->session->foo));

        $this->session->foo = 'bar';

        $this->assertEquals('bar', $this->session->foo);
        $this->assertEquals(1, count($this->session));
        $this->assertTrue(isset($this->session->foo));
        $this->assertFalse(empty($this->session->foo));
        $this->assertFalse(empty($_SESSION[Session::KEY][__CLASS__]));

        unset($this->session->foo);

        $this->assertEquals(0, count($this->session));
        $this->assertFalse(isset($this->session->foo));
        $this->assertTrue(empty($this->session->foo));
        $this->assertTrue(empty($_SESSION[Session::KEY][__CLASS__]));
    }

    public function testIterator() {
        $this->assertFalse($this->session->valid());

        $data = [1, 2, ['foo' => 'bar']];
        $this->session->fromArray($data);

        $this->assertNull($this->session->rewind());

        $this->assertTrue($this->session->valid());
        $this->assertEquals(0, $this->session->key());
        $this->assertEquals(1, $this->session->current());

        $this->assertNull($this->session->next());

        $this->assertTrue($this->session->valid());
        $this->assertEquals(1, $this->session->key());
        $this->assertEquals(2, $this->session->current());

        $this->assertNull($this->session->next());

        $this->assertTrue($this->session->valid());
        $this->assertEquals(2, $this->session->key());
        $this->assertEquals(['foo' => 'bar'], $this->session->current());

        $this->assertNull($this->session->next());

        $this->assertFalse($this->session->valid());
    }

    public function testArrayAccess() {
        $this->session['foo'] = [];
        $this->session['foo'][] = 'something';
        $this->assertEquals('something', $this->session['foo'][0]);
    }
}
