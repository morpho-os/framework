<?php
namespace Morpho\Code;

use Morpho\Base\Object;

class Annotation extends Object {
    public function toArray() {
        $result = parent::toArray();
        foreach ($result as $key => $value) {
            $result[$key] = $this->objectsToArray($value);
        }
        return $result;
    }

    protected function objectsToArray($value) {
        if (is_object($value)) {
            $r = $value->toArray();
        } elseif (is_array($value)) {
            $r = array();
            foreach ($value as $k => $v) {
                $r[$k] = $this->objectsToArray($v);
            }
        } else {
            $r = $value;
        }
        return $r;
    }
}
