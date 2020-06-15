<?php declare(strict_types=1);
namespace Morpho\Test\Manual;

// Tests related to https://github.com/morpho-os/framework/issues/413

require __DIR__ . '/init.php';

var_dump('Temp directory:', sys_get_temp_dir());

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Morpho\Network\Http\Browser;
use Morpho\Network\Http\SeleniumServer;
use Morpho\Testing\Sut;

$sut = Sut::instance();
$uri = $sut->siteUri();

function browsers() {
    $desiredCapabilities = DesiredCapabilities::firefox();
    $desiredCapabilities->setCapability('moz:firefoxOptions', ['args' => ['-headless']]);
    $browser = Browser::mk($desiredCapabilities);
    return [$browser];
}

$seleniumServerConfig = $sut->seleniumServerConfig();
var_dump('Selenium server config:', $seleniumServerConfig);
$seleniumServer = SeleniumServer::mk($seleniumServerConfig);
$seleniumServer->start();

var_dump('Site URI:', $uri);
foreach (browsers() as $browser) {
    var_dump('Browser capabilities:', $browser->getCapabilities());

    $testUri = $uri . '/localhost/test';
    var_dump('Checking URI:', $testUri);

    $browser->get($testUri);
    $testingResultsSelector = WebDriverBy::id('testing-results');
    $visibleElements = $browser->wait()->until(WebDriverExpectedCondition::visibilityOfAnyElementLocated($testingResultsSelector));
    var_dump('Visible elements:', $visibleElements);

    preg_match_all('~<script.*?src="([^"]+)">~si', file_get_contents($testUri), $match);
    assert(count($match[1]));
    var_dump('Found scripts:', count($match[1]));
    foreach ($match[1] as $scriptUri) {
        var_dump('--------------------------------------------------------------------------------', $scriptUri, file_get_contents($uri . $scriptUri));
    }
}
