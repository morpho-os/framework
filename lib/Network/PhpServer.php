<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network;

use function Morpho\Base\waitUntilNoOfAttempts;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class PhpServer implements IServer {
    private $address;
    private $docRootDirPath;
    //private $pid;
    private $actualAddress;
    /**
     * @var Process
     */
    private $process;

    public function __construct(TcpAddress $address, string $docRootDirPath) {
        $this->address = $address;
        $this->docRootDirPath = $docRootDirPath;
    }

    public function start() {
        $this->actualAddress = $address = null === $this->address->port()
            ? TcpSocket::findFreePort($this->address)
            : $this->address;
        $cmd = [
            $this->phpBinFilePath(),
            '-S', $address->host() . ':' . $address->port(),
            '-t', $this->docRootDirPath
        ];
        $process = new Process($cmd, $this->docRootDirPath);
        $process->setTimeout(0);
        $process->start();
        if (!$process->isRunning()) {
            throw new \RuntimeException('Unable to start the server process.');
        }
        $this->process = $process;
        waitUntilNoOfAttempts(function () {
          if (!$this->process->isRunning()) {
              throw new \RuntimeException("Process has exited with the error: " . \rtrim($this->process->getErrorOutput()) . " (exit code: {$this->process->getExitCode()})");
          }
          return $this->isListening();
        }, 500000, 20);
        return $address;
    }

    /**
     * Some code based \Symfony\Component\Process\Process::stop() ((c) Fabien Potencier <fabien@symfony.com>, Symfony project))
     */
    public function stop(): void {
        $this->process->stop();
        /*
        $this->process->signal(2); // Try send the SIGINT first
        try {
            waitUntilNoOfAttempts(function () {
                return $this->process->isRunning();
            }, 500000, 10);
        } catch (\RuntimeException $e) {
            // then try to send other signals.
            $this->process->stop();
        }
        */
    }

    public function isStarted(): bool {
        if (null === $this->process) {
            return false;
        }
        return $this->process->isRunning() && $this->isListening();
    }

    public function pid(): ?int {
        return $this->process->getPid();
    }

    protected function phpBinFilePath(): string {
        $phpFinder = new PhpExecutableFinder();
        if (false === $phpBinFilePath = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable');
        }
        return $phpBinFilePath;
    }

    protected function isListening(): bool {
        return TcpSocket::isListening($this->actualAddress);
    }
}