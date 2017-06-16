<?php
declare(strict_types=1);
namespace MorphoTest\Functional;

use Morpho\Test\BrowserTest;

class InstallerTest extends BrowserTest {
    public function testFoo() {
        $this->browser->get($this->baseUri);
        //$this->browser->findElement(WebDriverBy::id('db'))->sendKeys('foo');
        $this->assertEquals('Installation', $this->browser->getTitle());
    }
}