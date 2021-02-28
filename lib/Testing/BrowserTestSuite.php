<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Net\Http\GeckoDriver;
use Morpho\Net\Http\IWebDriver;

abstract class BrowserTestSuite extends TestSuite {
    public static function startWebDriver(Sut $sut, bool $once = true): IWebDriver {
        if ($once && isset($sut['webDriver'])) {
            return $sut['webDriver'];
        }
        $webDriverConf = $sut->webDriverConf();
        $webDriver = GeckoDriver::downloadMk($webDriverConf['geckoBinFilePath'], $sut->testRcDirPath());
        $webDriver->start();
        $sut['webDriver'] = $webDriver;
        return $webDriver;
    }

    public static function stopWebDriver(Sut $sut): void {
        if (isset($sut['webDriver'])) {
            $sut['webDriver']->stop();
        }
    }
}
