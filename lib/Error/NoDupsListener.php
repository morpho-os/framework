<?php
namespace Morpho\Error;

/**
 * Basic idea and code was found at:
 * @link https://github.com/DmitryKoterov/debug_errorhook.
 */
class NoDupsListener implements IExceptionListener {
    const DEFAULT_PERIOD = 300;  // 5 min.
    const ERROR_FILE_EXT = ".error";
    const GC_PROBABILITY = 0.01;

    protected $listener;

    protected $lockFileDirPath;

    protected $period;

    protected $gcExecuted = false;

    public function __construct(IExceptionListener $listener, string $lockFileDirPath = null, int $periodSec = null) {
        if (null === $lockFileDirPath) {
            $lockFileDirPath = $this->defaultLockFileDirPath();
        }
        $this->lockFileDirPath = $this->initLockFileDir($lockFileDirPath);

        $this->period = null !== $periodSec ? $periodSec : self::DEFAULT_PERIOD;
        $this->listener = $listener;
    }

    public function onException(\Throwable $exception): void {
        $id = $this->createLockId($exception);

        if ($this->isLockExpired($id, $exception)) {
            $this->listener->onException($exception);
        }

        // Touch always, even if we did not send anything. Else same errors will
        // be mailed again and again after $period (e.g. once per 5 minutes).
        $this->touch($id, $exception->getFile(), $exception->getLine());
    }

    protected function touch($id, $errFilePath, $errLine) {
        $filePath = $this->lockFilePath($id);
        file_put_contents($filePath, "$errFilePath:$errLine");
        @chmod($filePath, 0666);
        $this->gc();
    }

    protected function createLockId(\Throwable $e): string {
        $file = $e->getFile();
        $line = $e->getLine();
        $id = md5(
            join(
                ':',
                [
                    get_class($e),
                    $file,
                    $line,
                ]
            )
        );
        return $id;
    }

    protected function defaultLockFileDirPath() {
        return sys_get_temp_dir();
    }

    protected function initLockFileDir(string $dirPath): string {
        $dirPath = rtrim($dirPath, '\\/') . "/" . strtolower(str_replace('\\', '-', get_class($this)));
        if (!@is_dir($dirPath)) {
            if (!@mkdir($dirPath, 0777, true)) {
                $error = error_get_last();
                error_clear_last();
                throw new \Exception("Unable to create directory '{$dirPath}': {$error['message']}");
            }
        }
        return $dirPath;
    }

    protected function isLockExpired($id, \Throwable $exception) {
        $filePath = $this->lockFilePath($id);
        return !file_exists($filePath) || (filemtime($filePath) < time() - $this->period);
    }

    protected function lockFilePath($id) {
        return $this->lockFileDirPath . '/' . $id . self::ERROR_FILE_EXT;
    }

    protected function gc() {
        if ($this->gcExecuted || mt_rand(0, 10000) >= $this->gcProbability() * 10000) {
            return;
        }
        foreach (glob("{$this->lockFileDirPath}/*" . self::ERROR_FILE_EXT) as $filePath) {
            if (filemtime($filePath) <= time() - $this->period * 2) {
                @unlink($filePath);
            }
        }
        $this->gcExecuted = true;
    }

    protected function gcProbability() {
        return self::GC_PROBABILITY;
    }
}
