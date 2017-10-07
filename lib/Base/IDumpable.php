<?php
//declare(strict_types=1);
namespace Morpho\Base;

/**
 * An object which can be converted to a form useful for debugging.
 */
interface IDumpable {
    public function dump(): string;
}