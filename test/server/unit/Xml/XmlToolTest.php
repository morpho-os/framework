<?php
namespace MorphoTest\Xml;

use Morpho\Test\TestCase;
use Morpho\Xml\XmlTool;

class XmlToolTest extends TestCase {
    public function testArrayToDomDoc() {
        $data = [
            'student_info' => [
                'total_stud' => 500,
                0            => [
                    'student' => [
                        'id'      => 1,
                        'name'    => 'abc',
                        'address' => [
                            'city' => 'Pune',
                            'zip'  => 411006,
                        ],
                    ],
                ],
                1            => [
                    'student' => [
                        'id'      => 2,
                        'name'    => 'xyz',
                        'address' => [
                            'city' => 'Mumbai',
                            'zip'  => 400906,
                        ],
                    ],
                ],
            ],
        ];
        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<student_info>
  <total_stud>500</total_stud>
  <student>
    <id>1</id>
    <name>abc</name>
    <address>
      <city>Pune</city>
      <zip>411006</zip>
    </address>
  </student>
  <student>
    <id>2</id>
    <name>xyz</name>
    <address>
      <city>Mumbai</city>
      <zip>400906</zip>
    </address>
  </student>
</student_info>
XML;
        $this->assertEquals(trim($expected), trim(XmlTool::arrayToDomDoc($data)->saveXml()));
    }
}
