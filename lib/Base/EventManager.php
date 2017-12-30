<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

class EventManager implements IEventManager {
    /**
     * @var array
     */
    protected $handlers = [];

    public function on(string $eventName, callable $handler): void {
        $this->handlers[$eventName][] = $handler;
    }

    /**
     * @param callable|null $handlerSelector Selects which handlers must be deleted.
     */
    public function off(string $eventName, callable $handlerSelector = null): void {
        if (null === $handlerSelector) {
            unset($this->handlers[$eventName]);
        } else {
            if (!isset($this->handlers[$eventName])) {
                return;
            }
            foreach ($this->handlers[$eventName] as $key => $handler1) {
                if ($handlerSelector($handler1)) {
                    unset($this->handlers[$eventName][$key]);
                    return;
                }
            }
        }
    }

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
            if (null !== $result) {
                return $result;
            }
        }
    }
}