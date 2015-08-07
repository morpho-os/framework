<?php
namespace Morpho\Log;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream as StreamWriter;

/**
 * Preconfigured logger to log to console (cmd.exe or bash).
 */
class ConsoleLogger extends Logger implements ILogger {
    const DEFAULT_STREAM = 'php://stdout';
    const DEFAULT_INPUT_ENCODING = 'utf-8';
    const DEFAULT_OUTPUT_ENCODING = 'utf-8';
    const DEFAULT_LINE_SEPARATOR = "\n";

    /**
     * @param array $options Available keys are:
     * - 'format' (string)
     * - 'inputEncoding' (string)
     * - 'outputEncoding' (string)
     * - 'writeTo' (string|\Zend\Log\Writer\WriterInterface)
     */
    public function __construct(array $options = array()) {
        $defaultOptions = array(
            'format' => IconvFormatter::DEFAULT_FORMAT,
            'inputEncoding' => self::DEFAULT_INPUT_ENCODING,
            'outputEncoding' => self::DEFAULT_OUTPUT_ENCODING,
            'writeTo' => self::DEFAULT_STREAM,
            'lineSeparator' => self::DEFAULT_LINE_SEPARATOR,
        );
        $keys = array_diff_key($options, $defaultOptions);
        if (count($keys)) {
            throw new \InvalidArgumentException("Invalid options were provided.");
        }
        $options = array_merge($defaultOptions, $options);

        parent::__construct();

        $writer = is_object($options['writeTo'])
            ? $options['writeTo']
            : new StreamWriter($options['writeTo'], null, $options['lineSeparator']);
        unset($options['writeTo'], $options['lineSeparator']);
        $writer->setFormatter(new IconvFormatter($options));
        $this->addWriter($writer);
    }
}
