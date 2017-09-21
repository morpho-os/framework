<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Core;
use Morpho\Base\IFn;

class EventsMetaProvider implements IFn {
    public const DEFAULT_PRIORITY = 0;

    public function __invoke($module): iterable {
        $rClass = new \ReflectionClass($module);
        $rClasses = [$rClass];
        while ($rClass = $rClass->getParentClass()) {
            $rClasses[] = $rClass;
        }
        $rClasses = array_reverse($rClasses);
        $regexp = '~@Listen\s+(?<eventName>[a-zA-Z_][a-zA-Z_0-9]*)(\s+(?<priority>-?\d+))?~s';
        $foundEvents = [];
        $magicMethods = ['__call', '__callStatic', '__clone', '__construct,', '__debugInfo ', '__get', '__invoke', '__isset', '__set', '__set_state', '__sleep', '__toString', '__unset', '__wakeup', '_destruct'];
        foreach ($rClasses as $rClass) {
            $filter = \ReflectionMethod::IS_PUBLIC ^ (\ReflectionMethod::IS_ABSTRACT | \ReflectionMethod::IS_STATIC);
            foreach ($rClass->getMethods($filter) as $rMethod) {
                $methodName = $rMethod->getName();
                if (in_array($methodName, $magicMethods)) {
                    continue;
                }
                $docComment = $rMethod->getDocComment();
                if (false !== $docComment) {
                    if (preg_match_all($regexp, $docComment, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            $eventName = $match['eventName'];
                            $priority = isset($match['priority']) ? $match['priority'] : 0;
                            $foundEvents[$methodName][$eventName] = $priority;
                        }
                        continue;
                    }
                }
            }
        }
        $events = [];
        foreach ($foundEvents as $methodName => $events1) {
            foreach ($events1 as $eventName => $priority) {
                $events[] = [
                    'name'     => $eventName,
                    'priority' => (int)$priority,
                    'method'   => $methodName,
                ];
            }
        }
        return $events;
    }
}