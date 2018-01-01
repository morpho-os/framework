<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Base\NotImplementedException;
use Morpho\Core\IResponse;
use Morpho\Core\Request;
use Morpho\Core\IMessage;

class RequestTest extends MessageTest {
    private $request;

    public function setUp() {
        parent::setUp();
        $this->request = new class extends Request {
            protected function newResponse(): IResponse {
                throw new NotImplementedException(__METHOD__);
            }
        };
    }

    protected function newMessage(): IMessage {
        return clone $this->request;
    }
}