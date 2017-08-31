<?php declare(strict_types=1);
namespace Morpho\Infra;

require __DIR__ . '/../vendor/autoload.php';

$a = [
    'all',
    'any',
    'append',
    'contains',
    'filter',
    'head',
    'init',
    'last',
    'map',
    'prepend',
    'reduce',
    'tail',
];

$b = [
    'EmptyList',
    'String_WithSeparator',
    'String_WithoutSeparator',
    'Bytes',
    'Array_NumericKeys',
    'Array_StringKeys',
    'Iterator_NumericKeys',
    'Iterator_StringKeys',
    'Generator_NumericKeys',
    'Generator_StringKeys',
];

$res = [];
$j = 0;
foreach (\Morpho\Base\ArrayTool::cartesianProduct($a, $b) as $i => $pair) {
    if ($i % count($b) === 0) {
        $res[] = "    // --------------------------------------------------------------------------------\n"
            . '    // ' . $a[$j];
        $j++;
    }
    $res[] = '    public function test' . ucfirst($pair[0])  . '_' . $pair[1] . '() {' . "\n        \$this->markTestIncomplete();\n    }";
}
echo implode("\n\n", $res);