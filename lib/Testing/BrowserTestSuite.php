<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Network\Http\SeleniumServer;

abstract class BrowserTestSuite extends TestSuite {
    public static function startSeleniumServer(Sut $sut) {
        $seleniumDirPath = $sut->seleniumDirPath();
        $seleniumServer = SeleniumServer::mk([
            'geckoBinFilePath' => $seleniumDirPath . '/geckodriver',
            'serverJarFilePath' => $seleniumDirPath . '/selenium-server-standalone.jar',
            'serverVersion' => null,
            'logFilePath' => $seleniumDirPath . '/selenium.log',
        ]);
        $seleniumServer->start();
        $sut['seleniumServer'] = $seleniumServer;
    }

    public static function stopSeleniumServer(Sut $sut) {
        if (isset($sut['seleniumServer'])) {
            $sut['seleniumServer']->stop();
        }
    }

    public static function startSeleniumServerOnce(Sut $sut) {
        if (isset($sut['seleniumServer'])) {
            return; // Assume already started
        }
        self::startSeleniumServer($sut);
    }
}
