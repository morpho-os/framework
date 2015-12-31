<?php
namespace Morpho\Base;

class ArrayObject extends \ArrayObject {
    public function __construct(array $input = []) {
        parent::__construct($input, \ArrayObject::ARRAY_AS_PROPS | \ArrayObject::STD_PROP_LIST);
    }

    public function toArray() {
        return $this->getArrayCopy();
    }
}
