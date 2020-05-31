<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Network;

use Morpho\Network\PhpServer;
use Morpho\Network\TcpAddress;
use Morpho\Testing\TestCase;

class PhpServerTest extends TestCase {
    public function testStartAndStop() {
        $host = '127.0.0.1';
        $address = new TcpAddress($host, null); // use numeric address to avoid binding with IPv6.

        $docRootDirPath = $this->createTmpDir();
        \file_put_contents($docRootDirPath . '/index.php', "<?php die('hello');");

        $server = new PhpServer($address, $docRootDirPath);

        $this->assertFalse($server->isStarted());

        $address = $server->start();

        $this->assertInstanceOf(TcpAddress::class, $address);
        $this->assertSame($host, $address->host());
        $this->assertMatchesRegularExpression('~^\d+$~', (string)$address->port());

        $this->assertMatchesRegularExpression('~^[1-9]\d*$~', (string)$server->pid());
        $this->assertTrue($server->isStarted());

        $handle = \fsockopen('tcp://' . $address->host() . ':' . $address->port());

        \fwrite($handle, "GET / HTTP/1.1\r\n\r\n");
        $response = '';
        while (!\feof($handle)) {
            $response .= \fgets($handle, 1024);
        }
        \fclose($handle);
        $this->assertMatchesRegularExpression('~^HTTP/\d+.\d+ 200 OK\r\n.*hello~s', $response);

        $server->stop();

        $this->assertFalse($server->isStarted());
    }
}
