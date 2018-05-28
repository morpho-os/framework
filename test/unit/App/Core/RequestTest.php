<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Core;

use Morpho\App\Core\IRequest;
use Morpho\Base\NotImplementedException;
use Morpho\App\Core\IResponse;
use Morpho\App\Core\Request;
use Morpho\App\Core\Message;

class RequestTest extends MessageTest {
    private $request;

    public function setUp() {
        parent::setUp();
        $this->request = new class extends Request {
            protected function newResponse(): IResponse {
                throw new NotImplementedException(__METHOD__);
            }

            public function arg($nameOrIndex) {
                throw new NotImplementedException(__METHOD__);
            }

            public function args($namesOrIndexes = null) {
                throw new NotImplementedException(__METHOD__);
            }
        };
    }

    protected function newMessage(): Message {
        return clone $this->request;
    }

    public function testInterface() {
        $this->assertInstanceOf(IRequest::class, $this->request);
    }
}
