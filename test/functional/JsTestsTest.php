<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Functional;

use Facebook\WebDriver\WebDriverBy as By;
use Morpho\Test\BrowserTestCase;

class JsTestsTest extends BrowserTestCase {
    public function testJs() {
        $this->browser()->get($this->uri('system/test?bot'));
        $by = By::id('testing-results');
        $this->browser()->waitUntilElementIsVisible($by);
        $numberOfFailedTests = $this->browser()->findElement($by)->getText();
        $this->assertEquals(0, $numberOfFailedTests);
    }
}