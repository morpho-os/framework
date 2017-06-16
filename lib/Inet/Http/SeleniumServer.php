<?php
namespace Morpho\Inet\Http;
use function Morpho\Cli\cmd;
use Morpho\Fs\FileNotFoundException;

// Uses external tools: lsof, nc, kill, killall, geckodriver, selenium-server-standalone.jar, java, printf, bash.
class SeleniumServer {
    private $logFilePath;

    public const PORT = 4444;
    private $serverJarFilePath;
    private $port;
    private $geckoBinFilePath;

    public function __construct() {
        $this->serverJarFilePath = __DIR__ . '/selenium-server-standalone.jar';
    }

    public function setServerJarFilePath(string $filePath): self {
        $this->serverJarFilePath = $filePath;
        return $this;
    }

    public function serverJarFilePath(): string {
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
            cmd(
                'java'
                . (' -Dwebdriver.gecko.bin=' . escapeshellarg($geckoBinFilePath))
                //. ($marionette ? '' : ' -Dwebdriver.firefox.marionette=false')
                . ' -jar ' . escapeshellarg($serverJarFilePath)
                . ($this->logFilePath ? ' -log ' . escapeshellarg($this->logFilePath()) : '')
                . ' &> /dev/null &'
            );
            $i = 0;
            do {
                usleep(200000);
                $i++;
            } while (!$this->listening() && $i < 25);
            if ($i == 25) {
                throw new \RuntimeException("Unable to start Selenium Server");
            }
        }
        return $this;
    }

    public function listening(): bool {
        $res = cmd('printf "GET / HTTP/1.1\r\n\r\n" | nc localhost ' . self::PORT, ['checkExitCode' => false, 'buffer' => true]);
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
        $pid = (int) trim((string) cmd("lsof -t -c java -a -i ':" . escapeshellarg($this->port()) . "' 2>&1", ['buffer' => true, 'checkExitCode' => false]));
        // ss -t -a -n -p state all '( sport = 4444 )'
        return $pid > 0 ? $pid : null;
    }
}