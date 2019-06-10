<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Morpho\Network\Http\SeleniumServer;

abstract class BrowserTestSuite extends TestSuite {
    public static function startSeleniumServer(\ArrayObject $sut) {
        $seleniumServer = SeleniumServer::mk([
            'geckoBinFilePath' => $sut['seleniumDirPath'] . '/geckodriver',
            'serverJarFilePath' => $sut['seleniumDirPath'] . '/selenium-server-standalone.jar',
            'serverVersion' => null,
            'logFilePath' => $sut['seleniumDirPath'] . '/selenium.log',
        ]);
        $seleniumServer->start();
        $sut['seleniumServer'] = $seleniumServer;
    }

    public static function stopSeleniumServer(\ArrayObject $sut) {
        if (isset($sut['seleniumServer'])) {
            $sut['seleniumServer']->stop();
        }
    }

    public static function startSeleniumServerOnce(\ArrayObject $sut) {
        if (isset($sut['seleniumServer'])) {
            return; // Assume already started
        }
        self::startSeleniumServer($sut);
    }
}
