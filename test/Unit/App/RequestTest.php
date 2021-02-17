<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App;

use Morpho\App\IRequest;
use Morpho\Base\NotImplementedException;
use Morpho\App\IResponse;
use Morpho\App\Request;
use Morpho\App\Message;

class RequestTest extends MessageTest {
    private $request;

    public function setUp(): void {
        parent::setUp();
        $this->request = new class extends Request {
            protected function mkResponse(): IResponse {
                throw new NotImplementedException(__METHOD__);
            }

            public function arg(string|int $nameOrIndex) {
                throw new NotImplementedException(__METHOD__);
            }

            public function args($namesOrIndexes = null): mixed {
                throw new NotImplementedException(__METHOD__);
            }
        };
    }

    protected function mkMessage(): Message {
        return clone $this->request;
    }

    public function testInterface() {
        $this->assertInstanceOf(IRequest::class, $this->request);
    }
}
