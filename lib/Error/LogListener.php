<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Error;

use Morpho\Base\IFn;
use Psr\Log\LoggerInterface as ILogger;

class LogListener implements IFn {
    protected $logger;

    public function __construct(ILogger $logger) {
        $this->logger = $logger;
    }

    public function __invoke($exception): void {
        $this->logger->emergency($exception, ['exception' => $exception]);
    }
}