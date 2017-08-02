<?php
declare(strict_types=1);
namespace Morpho\Network\Http;

use function Morpho\Base\filter;
use function Morpho\Cli\cmd;
use Morpho\Xml\Document;

// Based on https://github.com/jarib/selenium-travis/blob/master/selenium-webdriver/lib/selenium/server.rb
class SeleniumServerDownloader {
    public function __invoke(string $destDirPath): string {
        $version = $this->latestVersion();
        $downloadFileName = "selenium-server-standalone-$version.jar";
        if (!preg_match('/(\d+\.\d+)\./As', $version, $match)) {
            throw new \UnexpectedValueException();
        }
        $uri = "https://selenium-release.storage.googleapis.com/{$match[1]}/$downloadFileName";
        $destFilePath = $destDirPath . '/' . $downloadFileName;
        if (is_file($destFilePath)) {
            return $destFilePath;
        }
        cmd('curl -L -o ' . escapeshellarg($destFilePath) . ' ' . escapeshellarg($uri));
        return $destFilePath;
    }

    private function latestVersion(): string {
        /*
        $tmpFilePath = __DIR__ . '/test.xml';
        if (!is_file($tmpFilePath)) {
            $xml = file_get_contents($tmpFilePath);
            file_put_contents($tmpFilePath, $xml);
        } else {
        */
        $xml = file_get_contents('https://selenium-release.storage.googleapis.com');
        //}
        $doc = Document::fromString($xml);
        $doc->xPath()->registerNamespace('s3', 'http://doc.s3.amazonaws.com/2006-03-01');
        // "//Key[contains(text(), 'selenium-server-standalone-')]"
        $versions = filter(function (&$v, $k) {
            if (preg_match('~selenium-server-standalone-(\d+\.\d+\.\d+)\.jar~', $v->nodeValue, $m)) {
                $v = array_pop($m);
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
        return $latest;
    }
}
