<?php
//declare(strict_types=1);
namespace Morpho\Core;
use Morpho\Base\IFn;

class EventsMetaProvider implements IFn {
    public function __invoke($module): iterable {
        $rClass = new \ReflectionClass($module);
        $rClasses = [$rClass];
        while ($rClass = $rClass->getParentClass()) {
            $rClasses[] = $rClass;
        }
        $rClasses = array_reverse($rClasses);
        // @TODO: Use integers for priority, accept sign: "+"|"-"
        $regexp = '~@Listen\s+(?<eventName>[a-zA-Z_][a-zA-Z_0-9]*)(\s+(?<priority>(?:\d*\.\d+)|(?:\d+\.\d*)|(\d+)))?~s';
        $foundEvents = [];
        foreach ($rClasses as $rClass) {
            $filter = \ReflectionMethod::IS_PUBLIC ^ (\ReflectionMethod::IS_ABSTRACT | \ReflectionMethod::IS_STATIC);
            foreach ($rClass->getMethods($filter) as $rMethod) {
                $methodName = $rMethod->getName();
                if ($methodName === '__construct') {
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
                if ($rMethod->class === $rClass->name) {
                    // If the child class defines a method with the same name, don't inherit
                    // doc-comments.
                    unset($foundEvents[$methodName]);
                }
            }
        }
        $events = [];
        foreach ($foundEvents as $methodName => $events1) {
            foreach ($events1 as $eventName => $priority) {
                $events[] = [
                    'name'     => $eventName,
                    'priority' => $priority,
                    'method'   => $methodName,
                ];
            }
        }
        return $events;
    }
}