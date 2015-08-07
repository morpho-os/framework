<?php
namespace MorphoTest\Log;

use Morpho\Test\TestCase;
use Morpho\Log\ConsoleLogger;
use Zend\Log\Writer\Null as NullWriter;

class ConsoleLoggerTest extends TestCase {
    public function testLog() {
        $stream = fopen('php://memory', 'w+');
        $logger = new ConsoleLogger(
            array(
                'format' => '%message%',
                'writeTo' => $stream,
                'outputEncoding' => 'cp866',
            )
        );
        $logger->info('Привет, Мир!');

        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        $this->assertEquals(iconv('utf-8', 'cp866', "Привет, Мир!\n"), $output);
    }

    public function testAcceptsCustomWriterInConstructor() {
        $writer = new NullWriter();
        $logger = new ConsoleLogger(array('writeTo' => $writer));
        $this->assertSame($writer, $logger->getWriters()->current());
    }
}
