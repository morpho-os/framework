<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network\Http;
use function Morpho\Cli\cmd;
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

    public function setServerJarFilePath(string $filePath): self {
        $this->serverJarFilePath = $filePath;
        return $this;
    }

    public function serverJarFilePath(): ?string {
        return $this->serverJarFilePath;
    }

    public function setLogFilePath(string $filePath): self {
        $this->logFilePath = $filePath;
        return $this;
    }

    public function logFilePath(): ?string {
        return $this->logFilePath;
    }

    public function setPort(int $port): self {
        $this->port = $port;
        return $this;
    }

    public function port(): int {
        return $this->port ?: self::PORT;
    }

    public function setGeckoBinFilePath(string $filePath): self {
        $this->geckoBinFilePath = $filePath;
        return $this;
    }

    public function geckoBinFilePath(): string {
        if (null === $this->geckoBinFilePath) {
            $this->geckoBinFilePath = '/usr/bin/geckodriver';
        }
        return $this->geckoBinFilePath;
    }

    public function start(): self {
        $pid = $this->findPid();
        if (!$pid) {
            $geckoBinFilePath = $this->geckoBinFilePath();
            if (!is_file($geckoBinFilePath)) {
                throw new FileNotFoundException($geckoBinFilePath);
            }
            $serverJarFilePath = $this->serverJarFilePath();
            if (!is_file($serverJarFilePath)) {
                throw new FileNotFoundException($serverJarFilePath);
            }
            // java -Dwebdriver.gecko.bin=/usr/bin/geckodriver -jar /path/to/selenium-server-standalone.jar
            $cmd = 'java'
                . (' -Dwebdriver.gecko.driver=' . escapeshellarg($geckoBinFilePath))
                //. ($marionette ? '' : ' -Dwebdriver.firefox.marionette=false')
                . ' -jar ' . escapeshellarg($serverJarFilePath)
                . ($this->logFilePath ? ' -log ' . escapeshellarg($this->logFilePath()) : '')
                . ' &> /dev/null &';
            //showLn("Starting server: " . $cmd);
            proc_close(proc_open($cmd, [], $pipes));
            //cmd($cmd);
            $i = 0;
            do {
                //showLn("Server started, i == " . $i);
                usleep(200000);
                $i++;
            } while (!$this->listening() && $i < 25);
            if ($i == 25) {
                throw new \RuntimeException("Unable to start Selenium Server");
            }
            //showLn("Running tests...");
        }
        return $this;
    }

    public function listening(): bool {
        // @TODO: Use php sockets.
        $res = cmd('printf "GET / HTTP/1.1\r\n\r\n" | nc localhost ' . self::PORT, ['checkExitCode' => false, 'capture' => true]);
        return !$res->isError();
    }

    public function stop(): void {
        $pid = $this->findPid();
        if ($pid) {
            cmd('kill ' . intval($pid) . ' > /dev/null');
        }
        cmd('killall geckodriver &> /dev/null || true');
    }

    private function findPid(): ?int {
        $pid = (int) trim((string) cmd("lsof -t -c java -a -i ':" . escapeshellarg($this->port()) . "' 2>&1", ['capture' => true, 'checkExitCode' => false]));
        // ss -t -a -n -p state all '( sport = 4444 )'
        return $pid > 0 ? $pid : null;
    }
}