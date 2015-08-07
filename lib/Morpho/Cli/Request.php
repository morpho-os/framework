<?php
namespace Morpho\Cli;

use Morpho\Core\Request as BaseRequest;

/*
use Zend\Stdlib\Message;
use Zend\Stdlib\RequestInterface;
*/

class Request extends BaseRequest {
    public function __construct() {
        if (empty($_SERVER['argv'])) {
            throw new \RuntimeException("Empty argv.");
        }
        $this->initParams($_SERVER['argv']);

        //command --name|n(=[^\s]+)?

        //$args = $_SERVER['argv'];
    }

    protected function createResponse() {
        return new Response();
    }

    protected function initParams(array $args) {
        $this->setParam('scriptName', array_shift($args));
        $this->setParam('command', array_shift($args));
        //$tree = $this->parse($this->tokenize($args));
        //d($tree);
    }

    protected function tokenize(array $args) {
        //new \Morpho\Code\Lexer();
    }
}

/*
class CommandLineLexer extends Lexer
{
    public function __construct()
    {
        parent::__construct([
            '--'
        ]);
    }
}
*/