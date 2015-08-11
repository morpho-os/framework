<?php
namespace Morpho\Web\Messenger;

interface IMessageStorage extends \Countable, \Iterator, \ArrayAccess {
    public function clear();
}