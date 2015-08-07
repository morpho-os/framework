<?php
namespace Morpho\Mail;

use Morpho\Log\ILogger;
use Zend\Log\Logger;
use Zend\Log\Writer\Null as NullWriter;
use Zend\Mail\Storage\Message;
use Zend\Mail\Storage\Pop3 as Pop3Storage;
use Zend\Mail\Storage\AbstractStorage;
use FilterIterator;
use Countable;
use InvalidArgumentException;

/**
 * If you need filter messages the MessageIterator should be extended and
 * the isMessageAcceptable() method should be overloaded.
 */
class MessageIterator extends FilterIterator implements Countable {
    protected $messageNumber = 1;

    public function __construct($storage = null, ILogger $logger = null) {
        if (!is_object($storage)) {
            $storage = new Pop3Storage($storage);
        }
        if (!$storage instanceof AbstractStorage) {
            throw new InvalidArgumentException();
        }
        if (null === $logger) {
            $logger = new Logger();
            $logger->addWriter(new NullWriter());
        }
        $this->logger = $logger;
        parent::__construct($storage);
    }

    public function rewind() {
        $this->messageNumber = 1;
        $this->log(sprintf("Total messages found: %d.", $this->count()));
        parent::rewind();
    }

    public function next() {
        $this->messageNumber++;
        parent::next();
    }

    final public function accept() {
        $message = $this->current();
        $result = $this->isMessageAcceptable($message, $this->messageNumber);
        if ($result) {
            $this->log($this->describeMessage($result, $message, $this->messageNumber));
        } else {
            $this->log($this->describeMessage($result, $message, $this->messageNumber));
            // It seems like there is a bug in PHP (at least in <= 5.4.4):
            // next() method is not a called if the accept() returns false.
            // So we need increment messageNumber here.
            $this->messageNumber++;
        }

        return $result;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function count() {
        return $this->getInnerIterator()->count();
    }

    /**
     * @return bool
     */
    protected function isMessageAcceptable(Message $message, $messageNumber) {
        return true;
    }

    /**
     * @param bool $isAccepted Either the message was accepted.
     * @param Message $message
     * @param int $messageNumber Range 1..Infinity
     *
     * @return string
     *     Should return string that should describe message.
     */
    protected function describeMessage($isAccepted, Message $message, $messageNumber) {
        $messageAsString = 'The message: "' . $message->getHeader('subject')->toString()
            . ' (' . $message->getHeader('from')->toString() . ')" was '
            . ($isAccepted ? 'accepted' : 'rejected') . '.';

        return $messageAsString;
    }

    protected function log($string) {
        $this->logger->debug($string);
    }
}
