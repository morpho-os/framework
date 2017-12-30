<?php declare(strict_types=1);
namespace Morpho\Code\Parsing;

use PhpParser\ParserFactory;

function parseFile(string $filePath): ?array {
    return parse(file_get_contents($filePath));
}

function parse(string $str): ?array {
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    return $parser->parse($str);
}