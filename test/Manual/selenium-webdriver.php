<?php declare(strict_types=1);

namespace Morpho\Test\Manual;

// Tests related to https://github.com/morpho-os/framework/issues/413

require __DIR__ . '/init.php';

use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Morpho\Network\Http\Browser;
use Morpho\Network\Http\GeckoDriver;
use Morpho\Testing\Sut;

$sut = Sut::instance();
$uri = $sut->siteUri();

function browsers() {
    $mkFirefox = function () {
        $desiredCapabilities = DesiredCapabilities::firefox();
        $desiredCapabilities->setCapability('moz:firefoxOptions', ['args' => ['-headless']]);
        return Browser::mk($desiredCapabilities);
    };
    $mkChrome = function () {
        $desiredCapabilities = DesiredCapabilities::chrome();
        return Browser::mk($desiredCapabilities);
    };
    return [/*$mkChrome(), */ $mkFirefox()];
}

$seleniumServerConfig = $sut->seleniumServerConfig();
var_dump('Selenium server config:', $seleniumServerConfig);

$webDriver = new GeckoDriver($seleniumServerConfig['geckoBinFilePath']);
$exitCode = 0;
try {
    $webDriver->start();

    var_dump('Site URI:', $uri);
    foreach (browsers() as $browser) {
        var_dump('Browser capabilities:', $browser->getCapabilities());

        $testUri = $uri . '/localhost/test';
        var_dump('Checking URI:', $testUri);

        $browser->get($testUri);
        $testingResultsSelector = WebDriverBy::id('testing-results');
        try {
            $visibleElements = $browser->wait()->until(WebDriverExpectedCondition::visibilityOfAnyElementLocated($testingResultsSelector));
            var_dump('Visible elements:', $visibleElements);
        } catch (TimeOutException $e) {
            var_dump('Timeout exception thrown:', $e);
            $exitCode = 1;
        }

        preg_match_all('~<script.*?src="([^"]+)">~si', file_get_contents($testUri), $match);
        assert(count($match[1]));
        var_dump('Found scripts:', count($match[1]));
        foreach ($match[1] as $scriptUri) {
            var_dump('--------------------------------------------------------------------------------', $scriptUri, file_get_contents($uri . $scriptUri));
        }
    }
} finally {
    $webDriver->stop();
}

exit($exitCode);