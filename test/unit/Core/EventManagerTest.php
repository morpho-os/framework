<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Core\Event;
use Morpho\Core\EventManager;
use Morpho\Test\TestCase;

class EventManagerTest extends TestCase {
    public function testMultipleHandlers_NoStop() {
        $eventManager = new EventManager();
        $eventName = 'foo';
        $eventManager->on($eventName, function () use (&$firstHandlerArgs) {
            $firstHandlerArgs = func_get_args();
        });
        $eventManager->on($eventName, function () use (&$secondHandlerArgs) {
            $secondHandlerArgs = func_get_args();
        });
        $event = new Event($eventName, ['test' => 123]);

        $eventManager->trigger($event);

        $this->assertSame([$event], $firstHandlerArgs);
        $this->assertSame([$event], $secondHandlerArgs);
    }

    public function testMultipleHandlers_StopAfterFirst() {
        $eventManager = new EventManager();
        $eventName = 'foo';
        $eventManager->on($eventName, function () use (&$firstHandlerArgs) {
            $firstHandlerArgs = func_get_args();
            return false;
        });
        $eventManager->on($eventName, function () {
            $this->fail('Must not be called');
        });
        $event = new Event($eventName, ['test' => 123]);

        $eventManager->trigger($event);

        $this->assertSame([$event], $firstHandlerArgs);
    }
}