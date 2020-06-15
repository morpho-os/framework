<?php declare(strict_types=1);
namespace Morpho\Test\Manual;

error_reporting(E_ALL);
ini_set('display_errors', '1');

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Morpho\Network\Http\Browser;
use Morpho\Network\Http\SeleniumServer;
use Morpho\Testing\Sut;

require __DIR__ . '/../../vendor/autoload.php';

$sut = Sut::instance();
$seleniumServerConfig = $sut->seleniumServerConfig();
var_dump('Selenium server config:', $seleniumServerConfig);
$seleniumServer = SeleniumServer::mk($seleniumServerConfig);
$seleniumServer->start();

$desiredCapabilities = DesiredCapabilities::firefox();
$desiredCapabilities->setCapability('moz:firefoxOptions', ['args' => ['-headless']]);
var_dump('Browser capabilities:', $desiredCapabilities->toArray());
$browser = Browser::mk($desiredCapabilities);

$uri = $sut->siteUri();
var_dump('Site URI:', $uri);

$browser->get($uri . '/localhost/test');
$testingResultsSelector = WebDriverBy::id('testing-results');
$visibleElements = $browser->wait()->until(WebDriverExpectedCondition::visibilityOfAnyElementLocated($testingResultsSelector));
var_dump('Visible elements:', $visibleElements);
