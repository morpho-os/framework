<?php
namespace MorphoTest\Mail;

use Morpho\Mail\MessageIterator;
use Morpho\Test\TestCase;
use Zend\Mail\Storage\Message;

class MessageIteratorTest extends TestCase {
    public function testAcceptsCustomerLoggerInConstructor() {
        $logger = new \Morpho\Test\Logger();
        $storage = $this->getMock('\Zend\Mail\Storage\Pop3', array(), array(), '', false);
        $it = new MessageIterator($storage, $logger);
        $this->assertSame($logger, $it->getLogger());
    }

    public function testLogsProcess() {
        $logger = new \Morpho\Test\Logger();

        $storageMock = $this->getMock('\Zend\Mail\Storage\Pop3', array(), array(), '', false);
        $storageMock->expects($this->any())
            ->method('countMessages')
            ->will($this->returnValue(3));
        $storageMock->expects($this->any())
            ->method('valid')
            ->will($this->onConsecutiveCalls(true, true, true, false));

        $storageMock->expects($this->any())
            ->method('current')
            ->will($this->returnValue(new Message(array())));

        $storageMock->expects($this->any())
            ->method('count')
            ->will($this->returnValue(3));

        $it = new MyMessageIterator($storageMock, $logger);
        foreach ($it as $message) {

        }
        $expected = array(
            "Total messages found: 3.",
            "The 1 message was accepted.",
            "The 2 message was rejected.",
            "The 3 message was accepted.",
        );
        $actual = $logger->getOutput();
        $this->assertEquals($expected, $actual);
    }

    public function testCount() {
        $storageMock = $this->getMock('\Zend\Mail\Storage\Pop3', array(), array(), '', false);
        $storageMock->expects($this->any())
            ->method('count')
            ->will($this->returnValue(3));
        $it = new MyMessageIterator($storageMock, new \Morpho\Test\Logger());
        $this->assertEquals(3, count($it));
    }
}

class MyMessageIterator extends MessageIterator {
    public function isMessageAcceptable(Message $message, $messageNumber) {
        return $messageNumber != 2;
    }

    public function describeMessage($isAccepted, Message $message, $messageNumber) {
        return 'The ' . $messageNumber . ' message was ' . ($isAccepted ? 'accepted' : 'rejected') . '.';
    }
}
