<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

interface IEventManager {
    public function on(string $eventName, callable $handler);

    public function trigger(string $eventName, array $args = null);
}