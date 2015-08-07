<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\HttpTool;

class HttpToolTest extends TestCase {
    public function setUp() {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'] = null;
    }

    public function testGetIpWithoutProxy() {
        $clientIp = '192.168.0.1';

        $_SERVER['REMOTE_ADDR'] = $clientIp;

        $this->assertEquals($clientIp, HttpTool::getIp(false));
    }

    public function testGetIpCanReturnFullProxyIp() {
        $proxyIp = '192.168.0.1, 10.0.0.1';

        $_SERVER['HTTP_X_FORWARDED_FOR'] = $proxyIp;

        $this->assertEquals($proxyIp, HttpTool::getIp(true));
    }

    public function testGetIpCanReturnProxyIpByIndex() {
        $index = -2;

        $ip = '192.168.0.1';

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, ' . $ip . ', 10.0.0.1';

        $this->assertEquals($ip, HttpTool::getIp(true, $index));
    }

    public function testThrowExceptionForEmptyIp() {
        $this->assertThrowsIpExceptionFor(true, 0);
        $this->assertThrowsIpExceptionFor(true, -1);
        $this->assertThrowsIpExceptionFor(true, 1);
    }

    public function testGetIpProxyBounds() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, 10.0.0.1';

        $this->assertEquals('127.0.0.1', HttpTool::getIp(true, -100));
        $this->assertEquals('127.0.0.1', HttpTool::getIp(true, -3));

        $this->assertEquals('127.0.0.1', HttpTool::getIp(true, -2));
        $this->assertEquals('10.0.0.1', HttpTool::getIp(true, -1));
        $this->assertEquals('127.0.0.1', HttpTool::getIp(true, 0));
        $this->assertEquals('10.0.0.1', HttpTool::getIp(true, 1));
        $this->assertEquals('10.0.0.1', HttpTool::getIp(true, 2));
        $this->assertEquals('10.0.0.1', HttpTool::getIp(true, 100));

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.0.1';
        $this->assertEquals('192.168.0.1', HttpTool::getIp(true, -100));
        $this->assertEquals('192.168.0.1', HttpTool::getIp(true, -1));
        $this->assertEquals('192.168.0.1', HttpTool::getIp(true, 0));
        $this->assertEquals('192.168.0.1', HttpTool::getIp(true, 1));
        $this->assertEquals('192.168.0.1', HttpTool::getIp(true, 100));
    }

    public function testGetIpThrowsExceptionWhenListOfIpsEmpty() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = ',';
        $this->assertThrowsIpExceptionFor(true, 0);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = ' , ,  , ';
        $this->assertThrowsIpExceptionFor(true, 0);
    }

    public function testGetIpCanReturnIpWhenSomeIpNotEmpty() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = ' , , 192.168.0.1  , ';
        $this->assertEquals('192.168.0.1', HttpTool::getIp(true, 2));
    }

    public function testGetIpThrowsExceptionWhenProxyIpEmpty() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = null;
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $this->assertThrowsIpExceptionFor(true, null);
    }

    public function testGetIpThrowsExceptionWhenIpEmpty() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';
        $_SERVER['REMOTE_ADDR'] = null;
        $this->assertThrowsIpExceptionFor(false, null);
    }

    private function assertThrowsIpExceptionFor($checkProxy, $index) {
        try {
            HttpTool::getIp($checkProxy, $index);
            $this->fail();
        } catch (\RuntimeException $ex) {
            $this->assertEquals('Unable to detect of the IP address.', $ex->getMessage());
        }
    }
}
