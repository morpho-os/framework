<?php
namespace Morpho\Test;

use Morpho\Log\ConsoleLogger;
use Zend\Log\Writer\Stream as StreamWriter;

/**
 * Logger that can be used in tests when you need test some logging functinality
 * that is compatable with \Morpho\Log\ILogger interface.
 */
class Logger extends ConsoleLogger {
    private $stream;

    public function __construct(array $options = array()) {
        $this->stream = fopen('php://memory', 'w+');
        $writer = new StreamWriter($this->stream, null, "\n");
        $options += array(
            'writeTo' => $writer,
            'format' => "%message%",
        );
        parent::__construct($options);
    }

    /**
     * @param  bool $asArray
     * @return string Returns all output that was produced as string.
     */
    public function getOutput($asArray = true) {
        rewind($this->stream);
        $output = stream_get_contents($this->stream);
        fclose($this->stream);
        if ($asArray) {
            if (!strlen($output)) {
                return array();
            }

            return explode("\n", rtrim($output, "\r\n"));
        }

        return $output;
    }
}
