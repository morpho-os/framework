<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Core;

use Morpho\Core\EventsMetaProvider;
use Morpho\Test\TestCase;

class EventsMetaProviderTest extends TestCase {
    public function testInvoke_EmptyModuleClass() {
        $eventsMetaProvider = new EventsMetaProvider();
        $meta = $eventsMetaProvider->__invoke(new EventsMetaProviderTest_EmptyModuleClass());
        $this->assertSame([], $meta);
    }

    public function testInvoke_ClassWithMultipleValidAnnotations() {
        $eventsMetaProvider = new EventsMetaProvider();
        $meta = $eventsMetaProvider->__invoke(new EventsMetaProviderTest_MultipleValidAnnotations());

        $this->assertSame([
            [
                'name' => 'foo',
                'priority' => -88,
                'method' =>  'foo',
            ],
            [
                'name' => 'foo',
                'priority' => -314,
                'method' => 'bar',
            ],
            [
                'name' => 'song',
                'priority' => 76871,
                'method' =>  'ear',
            ],
            [
                'name' => 'none',
                'priority' => 0,
                'method' =>  'zero',
            ],
            [
                'name' => 'none',
                'priority' => 0,
                'method' =>  'zero1',
            ],
            [
                'name' => 'none',
                'priority' => 0,
                'method' =>  'zero2',
            ],
        ], $meta);
    }

    public function dataForInvoke_InheritsCommentsIfTheSameMethodDoesNotHaveComments() {
        return [
            [
                new ChildWithTheSameMethodModule(),
            ],
            [
                new ChildWithoutMethodsModule()
            ],
        ];
    }

    /**
     * @dataProvider dataForInvoke_InheritsCommentsIfTheSameMethodDoesNotHaveComments
     */
    public function testInvoke_InheritsCommentsIfTheSameMethodDoesNotHaveComments($child) {
        $eventsMetaProvider = new EventsMetaProvider();
        $meta = $eventsMetaProvider->__invoke($child);
        $this->assertSame(
            [
                [
                    'name' => 'afterDispatch',
                    'priority' => -9999,
                    'method' => 'afterDispatch',
                ],
            ],
            $meta
        );
    }
}

class ParentModule {
    /**
     * @Listen afterDispatch -9999
     * @param array $event
     */
    public function afterDispatch(array $event) {

    }
}

class ChildWithTheSameMethodModule extends ParentModule {
    public function afterDispatch(array $event) {

    }
}

class ChildWithoutMethodsModule extends ParentModule {
    public function afterDispatch(array $event) {

    }
}

class EventsMetaProviderTest_EmptyModuleClass {
}

class EventsMetaProviderTest_MultipleValidAnnotations {
    /**
     * @Listen foo -88
     */
    public function foo() {
    }

    /**
     * Comment sample
     * @Listen foo -314
     */
    public function bar() {
    }

    /**
     * @Listen beforeDispatch -9999
     * @param $event
     * /
    public function beforeDispatch(array $event) {
    //$this->autoDecodeRequestJson();
    /*
    $request = $this->request;
    $header = $request->header('Content-Type');
    if (false !== $header && false !== stripos($header->getFieldValue(), 'application/json')) {
    $data = Json::decode($request->content());
    $request->replace((array) $data);
    }
    }
     */

    /**
     * Another
     * comment
     *
     * @return void
     *
     * @Listen song 76871
     */
    public function ear() {
    }

    /**
     * @Listen none 0
     * @return void 132
     */
    public function zero() {
    }

    /**
     * @Listen none
     * @return void 132
     */
    public function zero1() {
    }

    /**
     * @Listen none -0
     * @return void 132
     */
    public function zero2() {
    }
}
