<?php
namespace Morpho\Error;

use Zend\Mail\Message as MailMessage;
use Zend\Mail\Transport\Sendmail;

class MailListener implements IExceptionEventListener {
    protected $to;

    protected $from;

    protected $subject;

    /**
     * @param array|string $to
     * @param string $from
     * @param string $subject
     */
    public function __construct($to, $from = null, $subject = null) {
        $this->to = $to;
        $this->from = $from;
        $this->subject = $subject;
    }

    public function onException(\Throwable $exception) {
        $message = $this->createMailMessage();
        $message->setBody($exception->__toString());
        $message->setFrom($this->from);
        $message->setTo($this->to);
        if (null !== $this->subject) {
            $message->setSubject($this->subject);
        } else {
            $message->setSubject('An error has occurred');
        }
        $this->sendMailMessage($message);
    }

    protected function sendMailMessage(MailMessage $mailMessage) {
        (new Sendmail())->send($mailMessage);
    }

    protected function createMailMessage() {
        return new MailMessage();
    }
}
