<?php
namespace Morpho\Log;

/**
 * Preconfigured logger to log to cmd.exe.
 */
class CmdConsoleLogger extends ConsoleLogger implements ILogger {
    public function __construct(array $options = array()) {
        $options['outputEncoding'] = 'cp866';
        parent::__construct($options);
    }
}
