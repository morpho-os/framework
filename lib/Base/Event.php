<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

class Event extends \ArrayObject {
    public string $name;

    public array $args;

    public function __construct(string $name, array $args = []) {
        $this->name = $name;
        $this->args = $args;
    }
}
