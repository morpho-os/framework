<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

class Event extends \ArrayObject {
    /**
     * @var string
     */
    public $name;
    /**
     * @var array
     */
    public $args;

    public function __construct(string $name, array $args = []) {
        $this->name = $name;
        $this->args = $args;
    }
}