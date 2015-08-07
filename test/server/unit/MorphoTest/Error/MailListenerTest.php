<?php
namespace MorphoTest\Error;

use Morpho\Test\TestCase;
use Morpho\Error\ExceptionEvent;
use Morpho\Error\MailListener;
use Zend\Mail\Message as MailMessage;

class MailListenerTest extends TestCase {
    public function testSendsMailOnException() {
        $to = 'to@localhost';
        $from = 'from@localhost';
        $subject = 'My Subject';
        $listener = new MyMailListener($to, $from, $subject);
        $ex = new \Exception();
        $this->assertFalse($listener->mailSent);

        $listener->onException($ex);
        $this->assertTrue($listener->mailSent);
        $this->assertEquals($to, $listener->message->to);
        $this->assertEquals($from, $listener->message->from);
        $this->assertEquals($subject, $listener->message->subject);
        $this->assertNotEmpty($listener->message->body);
    }
}

class MyMailListener extends MailListener {
    public $mailSent = false;

    protected function sendMailMessage(MailMessage $mailMessage) {
        $this->mailSent = true;
    }

    protected function createMailMessage() {
        $this->message = new MyMailMessage();
        return $this->message;
    }
}

class MyMailMessage extends MailMessage {
    public $to, $from, $subject, $body;

    public function setTo($to, $name = null) {
        $this->to = $to;
    }

    public function setFrom($from, $name = null) {
        $this->from = $from;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function setBody($body) {
        $this->body = $body;
    }
}
