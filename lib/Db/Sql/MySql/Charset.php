<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

/**
 * Map<Char, Encoding>
 *     where
 *         Char is some character from finite alphabet of the charset
 *         and Encoding is usually integer number for this character. E.g. (taken from the MySQL manual):
 *             A = 0
 *             B = 1
 *             a = 2
 *             b = 3
 *             A, B, C, D - symbols/characters taken from some finite set (alphabet)
 *             0, 1, 2, 3 - encoding/codes of characters for the A,B,a,b respectively, i.e. 0 for A, 1 for B etc.
 */
abstract class Charset extends \ArrayObject {
}