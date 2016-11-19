<?php
namespace Morpho\Web\Logging;

use Monolog\Processor\WebProcessor as BaseWebProcessor;
use Morpho\Web\Environment;

class WebProcessor extends BaseWebProcessor {
    public function __invoke(array $record): array {
        $record = parent::__invoke($record);
        $record['extra'] = array_merge($record['extra'], Environment::clientIp());
        $record['extra']['userAgent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        return $record;
    }
}
