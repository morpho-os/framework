<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network\Mail;

use Zend\Mail\Message;
use Zend\Mail\Transport\Factory as TransportFactory;
use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\InMemory as InMemoryTransport;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\TransportInterface as ITransport;

class MailSender {
    protected $transport;

    protected $enableDiagnostics = true;

    private $lastTransport;

    /**
     * @var ?Message
     */
    private $lastMessage;

    public function send($fromEmail, $toEmail = null, string $subject = null, $body = null) {
        if (isset($fromEmail['to'])) {
            $toEmail = $fromEmail['to'];
            $subject = $fromEmail['subject'];
            $body = $fromEmail['body'] ?? '';
            $fromEmail = $fromEmail['from'];
        }
        $message = new Message();
        $message->setEncoding('utf-8');
        $message->setTo($toEmail)
            ->setFrom($fromEmail)
            ->setSubject($subject)
            ->setBody($body);
        $transport = $this->transport();
        if ($this->enableDiagnostics) {
            if ($transport instanceof SendmailTransport) {
                $this->lastMessage = $message;
            }
            $this->lastTransport = $transport;
        }
        return $transport->send($message);
    }

    /**
     * @param string|array|\Traversable $spec If not string then the valid keys are: 'type', 'options'.
     */
    public function useTransport($spec = []): self {
        $this->setTransport(TransportFactory::create(
            is_string($spec) ? ['type' => $spec] : $spec
        ));
        return $this;
    }

    public function diagnostics(): array {
        $diagnostics = [];
        $transport = $this->lastTransport;
        if (null === $transport) {
            return $diagnostics;
        }
        if ($transport instanceof SmtpTransport) {
            $diagnostics = ['log' => $transport->getConnection()->getLog()];
        } elseif ($transport instanceof FileTransport) {
            $filePath = $transport->getLastFile();
            if (is_file($filePath)) {
                $diagnostics = ['log' => file_get_contents($filePath), 'filePath' => $filePath];
            }
        } elseif ($transport instanceof InMemoryTransport) {
            $diagnostics = ['log' => $transport->getLastMessage()->toString()];
        } elseif ($transport instanceof SendmailTransport) {
            // @TODO: Can be null?
            $diagnostics = ['log' => $this->lastMessage->toString()];
        }
        return array_merge($diagnostics, ['transport' => get_class($transport)]);
    }

    public function setTransport(ITransport $transport) {
        $this->transport = $transport;
    }

    public function transport() {
        if (null === $this->transport) {
            $this->transport = new SendmailTransport();
        }
        return $this->transport;
    }
}