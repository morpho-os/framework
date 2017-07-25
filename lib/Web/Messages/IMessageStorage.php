<?php
namespace Morpho\Web\Messages;

interface IMessageStorage extends \Countable, \Iterator, \ArrayAccess {
    public function clear(): void;

    public function toArray(): array;
}