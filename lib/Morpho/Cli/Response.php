<?php
namespace Morpho\Cli;

use Zend\Stdlib\Message;
use Zend\Stdlib\ResponseInterface;

class Response extends Message implements ResponseInterface {
    public function send() {
        echo $this->getContent();
    }
}