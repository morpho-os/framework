<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Network\Http;

use Morpho\Network\IServer;
use function Morpho\App\Cli\argsStr;
use function Morpho\App\Cli\sh;
use Morpho\Base\Arr;
use Morpho\Fs\FileNotFoundException;
use function Morpho\Base\toArray;

// Uses external tools: lsof, nc, kill, killall, geckodriver, selenium-server-standalone.jar, java, printf, bash.
class SeleniumServer implements IServer {
    private ?string $logFilePath = null;

    public const PORT = 4444;
    private ?string $serverJarFilePath;
    private int $port = self::PORT;
    private string $geckoBinFilePath = '/usr/bin/geckodriver';

    /**
     * @param string $serverJarFilePath File path like __DIR__ . '/selenium-server-standalone.jar'
     */
    public function __construct(string $serverJarFilePath = null) {
        $this->serverJarFilePath = $serverJarFilePath;
    }

    public static function mk(array $config): SeleniumServer {
        $port = $config['port'] ?? self::PORT;
        $config = Arr::require($config, ['geckoBinFilePath', 'serverVersion', 'serverJarFilePath', 'logFilePath']);
        $geckoBinFilePath = $config['geckoBinFilePath'];
        $geckoBinFilePath = (new GeckoDriverDownloader())($geckoBinFilePath);
        $serverJarFilePath = SeleniumServerDownloader::download($config['serverVersion'], $config['serverJarFilePath']);
        $seleniumServer = new static($serverJarFilePath);
        $seleniumServer->setGeckoBinFilePath($geckoBinFilePath);
        $seleniumServer->setLogFilePath($config['logFilePath']);
        $seleniumServer->setPort($port);
        return $seleniumServer;
    }

    public function setServerJarFilePath(string $filePath): void {
        $this->serverJarFilePath = $filePath;
    }

    public function serverJarFilePath(): ?string {
        return $this->serverJarFilePath;
    }

    public function setLogFilePath(string $filePath): void {
        $this->logFilePath = $filePath;
    }

    public function logFilePath(): ?string {
        return $this->logFilePath;
    }

    public function setPort(int $port): void {
        $this->port = $port;
    }

    public function port(): int {
        return $this->port;
    }

    public function setGeckoBinFilePath(string $filePath): void {
        $this->geckoBinFilePath = $filePath;
    }

    public function geckoBinFilePath(): string {
        return $this->geckoBinFilePath;
    }

    public function start(): void {
        $pids = toArray($this->findPids());
        if (!count($pids)) {
            $geckoBinFilePath = $this->geckoBinFilePath();
            if (!\is_file($geckoBinFilePath)) {
                throw new FileNotFoundException($geckoBinFilePath);
            }
            $serverJarFilePath = $this->serverJarFilePath();
            if (!\is_file($serverJarFilePath)) {
                throw new FileNotFoundException($serverJarFilePath);
            }
            $trustStoreFilePath = /*$keyStoreFilePath =*/
                $serverJarFilePath . '.' . \uniqid('cacerts');
            // java -Dwebdriver.gecko.bin=/usr/bin/geckodriver -jar /path/to/selenium-server-standalone.jar
            $cmd = 'java'
                . ' -Djavax.net.ssl.trustStoreType=jks'
                . ' -Djavax.net.ssl.trustStore=' . \escapeshellarg($trustStoreFilePath) // To fix Facebook\WebDriver\Exception\UnknownServerException caused by invalid `cacerts` file
                //. ' -Djavax.net.ssl.keyStore=' . \escapeshellarg($keyStoreFilePath)
                . ' -Dwebdriver.gecko.driver=' . \escapeshellarg($geckoBinFilePath)
                //. ' -Dwebdriver.firefox.marionette=true'
                . ' -jar ' . \escapeshellarg($serverJarFilePath)
                . ($this->logFilePath ? ' -log ' . \escapeshellarg($this->logFilePath()) : '')
                . ' &> /dev/null &';
            //showLn("Starting server: " . $cmd);
            \proc_close(\proc_open($cmd, [], $pipes));
            //shell($cmd);
            $i = 0;
            do {
                \usleep(200000);
                $i++;
            } while (!$this->acceptingConnections() && $i < 25);
            if ($i == 25) {
                throw new \RuntimeException("Unable to start Selenium Server");
            }
        }
    }

    public function acceptingConnections(int $port = self::PORT): bool {
        // @TODO: Use php sockets.
        $res = sh('printf "GET / HTTP/1.1\r\n\r\n" | nc localhost ' . $port, ['checkCode' => false, 'capture' => true, 'show' => false]);
        return !$res->isError();
    }

    public function stop(): void {
        $pids = toArray($this->findPids());
        if (count($pids)) {
            sh('kill ' . argsStr($pids) . ' > /dev/null', ['show' => false]);
        }
        sh('killall geckodriver &> /dev/null || true', ['show' => false]);
    }

    private function findPids(): iterable {
        return sh('lsof -t -c java -a -i :' . \escapeshellarg((string)$this->port()) . ' 2>&1', ['capture' => true, 'checkCode' => false, 'show' => false])->lines();
    }

    public function __destruct() {
        $this->stop();
    }
}
