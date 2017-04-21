<?php
namespace Morpho\Base;

use ArrayObject as BaseArrayObject;

class ArrayObject extends BaseArrayObject {
    public function __construct(array $input = []) {
        parent::__construct($input, self::ARRAY_AS_PROPS | self::STD_PROP_LIST);
    }

    public function toArray() {
        return $this->getArrayCopy();
    }
}
