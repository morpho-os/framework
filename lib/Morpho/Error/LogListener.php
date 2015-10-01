<?php
namespace Morpho\Error;

use Psr\Log\LoggerInterface as ILogger;

class LogListener implements IExceptionListener {
    protected $logger;

    public function __construct(ILogger $logger) {
        $this->logger = $logger;
    }

    public function onException(\Throwable $exception) {
        $this->logger->emergency($exception->__toString(), ['exception' => $exception]);
    }
}