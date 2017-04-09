<?php
namespace Morpho\Base;

interface IEventManager {
    public function on(string $eventName, callable $handler);

    public function trigger(string $eventName, array $args = null);
}