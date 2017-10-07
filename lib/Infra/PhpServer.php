<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

use function Morpho\Base\waitUntilNoOfAttempts;
use Morpho\Network\Address;
use Morpho\Network\TcpServer;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class PhpServer {
    private $address;
    private $docRootDirPath;
    //private $pid;
    private $actualAddress;
    /**
     * @var Process
     */
    private $process;

    public function __construct(Address $address, string $docRootDirPath) {
        $this->address = $address;
        $this->docRootDirPath = $docRootDirPath;
    }

    public function start(): Address {
        $this->actualAddress = $address = null === $this->address->port
            ? $this->findFreePort($this->address)
            : $this->address;
        $cmd = [
            $this->phpBinFilePath(),
            '-S', $address->host . ':' . $address->port,
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
              throw new \RuntimeException("Process has exited with the error: " . rtrim($this->process->getErrorOutput()) . " (exit code: {$this->process->getExitCode()})");
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
        return TcpServer::isListening($this->actualAddress);
    }

    /**
     * NB: This method can return invalid result in an environment when other processes can start listening during running of this method.
     */
    protected function findFreePort(Address $address): Address {
        return Address::fromString(stream_socket_get_name(
            stream_socket_server("tcp://{$address->host}:0"),
            false
        ));
    }
}