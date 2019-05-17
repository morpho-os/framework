<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network\Http;
use function Morpho\App\Cli\shell;
use Morpho\Base\Arr;
use Morpho\Fs\FileNotFoundException;

// Uses external tools: lsof, nc, kill, killall, geckodriver, selenium-server-standalone.jar, java, printf, bash.
class SeleniumServer {
    private $logFilePath;

    public const PORT = 4444;
    private $serverJarFilePath;
    private $port;
    private $geckoBinFilePath;

    /**
     * @param string $serverJarFilePath File path like __DIR__ . '/selenium-server-standalone.jar'
     */
    public function __construct(string $serverJarFilePath = null) {
        $this->serverJarFilePath = $serverJarFilePath;
    }

    public static function mk(array $config): SeleniumServer {
        $port = $config['port'] ?? self::PORT;
        $config = Arr::requireItems($config, ['geckoBinFilePath', 'serverVersion', 'serverJarFilePath', 'logFilePath']);
        $geckoBinFilePath = $config['geckoBinFilePath'];
        $geckoBinFilePath = (new GeckoDriverDownloader())($geckoBinFilePath);
        $serverJarFilePath = SeleniumServerDownloader::download($config['serverVersion'] ?? SeleniumServerDownloader::latestVersion(), $config['serverJarFilePath']);
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
        return $this->port ?: self::PORT;
    }

    public function setGeckoBinFilePath(string $filePath): void {
        $this->geckoBinFilePath = $filePath;
    }

    public function geckoBinFilePath(): string {
        if (null === $this->geckoBinFilePath) {
            $this->geckoBinFilePath = '/usr/bin/geckodriver';
        }
        return $this->geckoBinFilePath;
    }

    public function start(): void {
        $pid = $this->findPid();
        if (!$pid) {
            $geckoBinFilePath = $this->geckoBinFilePath();
            if (!\is_file($geckoBinFilePath)) {
                throw new FileNotFoundException($geckoBinFilePath);
            }
            $serverJarFilePath = $this->serverJarFilePath();
            if (!\is_file($serverJarFilePath)) {
                throw new FileNotFoundException($serverJarFilePath);
            }
            $trustStoreFilePath = $serverJarFilePath . '.' . uniqid('cacerts');
            // java -Dwebdriver.gecko.bin=/usr/bin/geckodriver -jar /path/to/selenium-server-standalone.jar
            $cmd = 'java'
                . ' -Djavax.net.ssl.trustStore=' . \escapeshellarg($trustStoreFilePath) // To fix Facebook\WebDriver\Exception\UnknownServerException caused by invalid `cacerts` file
                . ' -Dwebdriver.gecko.driver=' . \escapeshellarg($geckoBinFilePath)
                //. ($marionette ? '' : ' -Dwebdriver.firefox.marionette=false')
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
            } while (!$this->listening() && $i < 25);
            if ($i == 25) {
                throw new \RuntimeException("Unable to start Selenium Server");
            }
        }
    }

    public function listening(): bool {
        // @TODO: Use php sockets.
        $res = shell('printf "GET / HTTP/1.1\r\n\r\n" | nc localhost ' . self::PORT, ['checkCode' => false, 'capture' => true, 'show' => false]);
        return !$res->isError();
    }

    public function stop(): void {
        $pid = $this->findPid();
        if ($pid) {
            shell('kill ' . \intval($pid) . ' > /dev/null', ['show' => false]);
        }
        shell('killall geckodriver &> /dev/null || true', ['show' => false]);
    }

    private function findPid(): ?int {
        $pid = (int) \trim((string) shell("lsof -t -c java -a -i ':" . \escapeshellarg((string)$this->port()) . "' 2>&1", ['capture' => true, 'checkCode' => false, 'show' => false]));
        // ss -t -a -n -p state all '( sport = 4444 )'
        return $pid > 0 ? $pid : null;
    }

    public function __destruct() {
        $this->stop();
    }
}
