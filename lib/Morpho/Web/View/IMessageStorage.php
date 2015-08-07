<?php
namespace Morpho\Web\View;

interface IMessageStorage extends \Countable, \Iterator, \ArrayAccess {
    public function clear();
}