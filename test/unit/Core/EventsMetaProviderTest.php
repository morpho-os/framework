<?php
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
