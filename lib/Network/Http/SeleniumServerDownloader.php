<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network\Http;

use function Morpho\Base\filter;
use function Morpho\App\Cli\sh;
use Morpho\Xml\Doc;

// Based on https://github.com/jarib/selenium-travis/blob/master/selenium-webdriver/lib/selenium/server.rb
class SeleniumServerDownloader {
    public static function download(string $version, string $destJarFilePath): string {
        if (is_file($destJarFilePath)) {
            return $destJarFilePath;
        }
        if (null === $version) {
            $version = self::latestVersion();
        }
        if (!\preg_match('/(\d+\.\d+)\./As', $version, $match)) {
            throw new \UnexpectedValueException();
        }
        $downloadFileName = "selenium-server-standalone-$version.jar";
        $uri = "https://selenium-release.storage.googleapis.com/{$match[1]}/$downloadFileName";
        sh('curl --silent -L -o ' . \escapeshellarg($destJarFilePath) . ' ' . \escapeshellarg($uri), ['show' => false]);
        return $destJarFilePath;
    }

    public static function latestVersion(): string {
        /*
        $tmpFilePath = __DIR__ . '/test.xml';
        if (!\is_file($tmpFilePath)) {
            $xml = file_get_contents($tmpFilePath);
            file_put_contents($tmpFilePath, $xml);
        } else {
        */
        $xml = \file_get_contents('https://selenium-release.storage.googleapis.com');
        //}
        $doc = Doc::parse($xml);
        $doc->xPath()->registerNamespace('s3', 'http://doc.s3.amazonaws.com/2006-03-01');
        // "//Key[contains(text(), 'selenium-server-standalone-')]"
        $versions = filter(function (&$v, $k) {
            if (\preg_match('~selenium-server-standalone-(\d+\.\d+\.\d+.*)\.jar~', $v->nodeValue, $m)) {
                $v = \array_pop($m);
                return true;
            }
            return false;
        }, $doc->select('//s3:Key'));
        $latest = null;
        foreach ($versions as $version) {
            if (null === $latest) {
                $latest = $version;
            } else {
                $res = $version <=> $latest;
                if ($res > 0) {
                    $latest = $version;
                }
            }
        }
        if (null === $latest) {
            throw new \RuntimeException("Unable to find the latest version");
        }
        /** @var string $latest */
        return $latest;
    }
}
