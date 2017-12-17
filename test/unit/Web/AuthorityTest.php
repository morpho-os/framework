<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Authority;

class AuthorityTest extends TestCase {
    public function testAuthority() {
        $authorityStr = 'foo:bar@example.com:80';
        $authority = new Authority($authorityStr);
        $this->assertSame('foo:bar', $authority->userInfo);
        $this->assertSame('example.com', $authority->host);
        $this->assertSame(80, $authority->port);
        $this->assertSame($authorityStr, $authority->toString());
    }
}