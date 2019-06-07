<?php declare(strict_types=1);
namespace Morpho\Test\Integration;

class TestCase extends \Morpho\Testing\BrowserTestCase {
    public function setUp(): void {
        parent::setUp();
        $sut = self::sut();
        if (empty($sut['seleniumServer'])) {
            TestSuite::startSeleniumServer($sut);
        }
    }
}
