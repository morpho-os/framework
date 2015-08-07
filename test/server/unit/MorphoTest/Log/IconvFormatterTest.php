<?php
namespace MorphoTest\Log;

use Morpho\Test\TestCase;
use Morpho\Log\IconvFormatter;

class IconvFormatterTest extends TestCase {
    public function testFormat() {
        $options = array(
            'format' => '%message%',
            'outputEncoding' => 'cp866',
        );
        $formatter = new IconvFormatter($options);
        $event = array(
            'message' => 'Привет, Мир!',
        );
        $this->assertEquals(iconv('utf-8', 'cp866', $event['message']), $formatter->format($event));
    }
}
