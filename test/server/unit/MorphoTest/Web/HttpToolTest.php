<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\HttpTool;

class HttpToolTest extends TestCase {
    public function setUp() {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'] = null;
    }

    public function testGetIp_WithoutCheckProxy() {
        $clientIp = '192.168.0.1';

        $_SERVER['REMOTE_ADDR'] = $clientIp;

        $this->assertEquals($clientIp, HttpTool::getIp(false));
    }

    public function dataForGetIp_WithoutProxy_IpV6() {
        return [
            [
                '::1',
            ],
            [
                '0000:0000:0000:0000:0000:0000:0000:0001',
            ],
            [
                '2001:0db8:0000:0042:0000:8a2e:0370:7334',
            ],
            [
                '2001:db8::ff00:42:8329',
            ],
        ];
    }

    /**
     * @dataProvider dataForGetIp_WithoutProxy_IpV6
     */
    public function testGetIp_WithoutProxy_IpV6($ip) {
        $_SERVER['REMOTE_ADDR'] = $ip;
        $this->assertEquals($ip, HttpTool::getIp(false));
    }

    /**
     * @dataProvider dataForGetIp_WithoutProxy_IpV6
     */
    public function testGetIP_WithProxy_IpV6($ip) {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $ip;
        $this->assertEquals($ip, HttpTool::getIp(true));
    }

    public function testGetIp_WithCheckProxy_CanReturnFullProxyIp() {
        $proxyIp = '192.168.0.1, 10.0.0.1';

        $_SERVER['HTTP_X_FORWARDED_FOR'] = $proxyIp;

        $this->assertEquals($proxyIp, HttpTool::getIp(true));
    }

    public function testGetIp_WithCheckProxy_CanReturnProxyIpByIndex() {
        $index = -2;

        $ip = '192.168.0.1';

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, ' . $ip . ', 10.0.0.1';

        $this->assertEquals($ip, HttpTool::getIp(true, $index));
    }

    public function testGetIp_WithCheckProxy_ProxyBounds() {
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

    public function testGetIp_WithCheckProxy_CanReturnIpWhenSomeIpNotEmpty() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = ' , , 192.168.0.1  , ';
        $this->assertEquals('192.168.0.1', HttpTool::getIp(true, 2));
    }
}
