<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

/**
 * Rules for comparison of two characters taken from a charset. There are different types of collation, some of them:
 *    binary - compare integer codes of characters (encoding)
 *    `ci/case insensitive` collation - apply toupper (or tolower), then compare using rules for binary collation
 */
class Collation {
    /**
     * Name of collation starts with charset, eg: utf8_general_ci | utf8mb4_unicode_ci | utf8mb4_general_ci | ...
     * collation-name = charset "_" collation "_" suffix
     *     suffix = "_ci" - case insensitive
     *               "_cs" - case sensitive
     *               "_bin" - binary; character comparisons are based on code of chars (encoding)
     */
    private string $name;

    private string $charsetName;

    public function __construct(string $name, string $charsetName) {
        $this->name = $name;
        $this->charsetName = $charsetName;
    }

    public function charsetName(): string {
        return $this->charsetName;
    }

    public function name(): string {
        return $this->name;
    }
}