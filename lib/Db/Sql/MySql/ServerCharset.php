<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

/**
 * Character set, [(char, encoding)], where char is some character from finite alphabet of the charset, and encoding is usually integer number for this character. E.g. (taken from the MySQL manual):
    A = 0
    B = 1
    a = 2
    b = 3
        A, B, C, D - symbols/characters taken from some finite set (alphabet)
        0, 1, 2, 3 - encoding/codes of characters for the A,B,a,b respectively, i.e. 0 for A, 1 for B etc.
 */
class ServerCharset {
    /**
     * E.g utf8 | utf8mb4 | ...
     * @var string
     */
    private $name;

    /**
     * Maximum number of bytes required to store one character.
     * @var int
     */
    private $charSize;

    private $description;

    private $defaultCollation;

    public function __construct(string $name, string $defaultCollation, int $charSize, string $description = null) {
        $this->name = $name;
        $this->defaultCollation = new ServerCollation($defaultCollation, $name, true);
        $this->charSize = $charSize;
        $this->description = $description;
    }

    public function name(): string {
        return $this->name;
    }

    public function charSize(): int {
        return $this->charSize;
    }

    public function description(): ?string {
        return $this->description;
    }

    /**
     * Each charset has own default collation.
     * Two different character sets cannot have the same collation.
     */
    public function defaultCollation(): ServerCollation {
        return $this->defaultCollation;
    }
}