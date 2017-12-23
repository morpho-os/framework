<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Infra;

use Morpho\Infra\PhpServer;
use Morpho\Network\Address;
use Morpho\Test\TestCase;

class PhpServerTest extends TestCase {
    public function testStartAndStop() {
        $host = '127.0.0.1';
        $address = new Address($host, null); // use numeric address to avoid binding with IPv6.

        $docRootDirPath = $this->createTmpDir();
        file_put_contents($docRootDirPath . '/index.php', "<?php die('hello');");

        $server = new PhpServer($address, $docRootDirPath);

        $this->assertFalse($server->isStarted());

        $address = $server->start();

        $this->assertInstanceOf(Address::class, $address);
        $this->assertSame($host, $address->host());
        $this->assertRegExp('~^\d+$~', (string)$address->port());

        $this->assertRegExp('~^[1-9]\d*$~', (string)$server->pid());
        $this->assertTrue($server->isStarted());

        $handle = fsockopen('tcp://' . $address->host() . ':' . $address->port());

        fwrite($handle, "GET / HTTP/1.1\r\n\r\n");
        $response = '';
        while (!feof($handle)) {
            $response .= fgets($handle, 1024);
        }
        fclose($handle);
        $this->assertRegExp('~^HTTP/\d+.\d+ 200 OK\r\n.*hello~s', $response);

        $server->stop();

        $this->assertFalse($server->isStarted());
    }
}