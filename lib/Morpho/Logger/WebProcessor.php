<?php
namespace Morpho\Logger;

use Monolog\Processor\WebProcessor as BaseWebProcessor;
use Morpho\Web\HttpTool;

class WebProcessor extends BaseWebProcessor
{
    public function __invoke(array $record)
    {
        $record = parent::__invoke($record);

        $record['extra']['ip'] = HttpTool::getIp() ?: HttpTool::UNKNOWN_IP;

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $record['extra']['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        return $record;
    }
}
