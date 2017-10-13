<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

class EventManager {
    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @return mixed
     */
    public function trigger(Event $event) {
        $name = $event->name;
        if (!isset($this->handlers[$name])) {
            return null;
        }
        foreach ($this->handlers[$name] as $handler) {
            $result = $handler($event);
            if (false === $result) {
                return $result;
            }
        }
    }

    public function on(string $name, callable $handler): void {
        $this->handlers[$name][] = $handler;
    }
}