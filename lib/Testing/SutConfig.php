<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

class SutConfig implements \ArrayAccess {
    private $values = [];

    protected $defaultValues = [
        'siteUri' => 'http://framework',
    ];

    public function offsetExists($name): bool {
        return \array_key_exists($name, $this->values) || \array_key_exists($name, $this->defaultValues);
    }

    public function offsetGet($name) {
        return \array_key_exists($name, $this->defaultValues) ? $this->defaultValues[$name] : $this->values[$name];
    }

    public function offsetSet($name, $value) {
        $this->values[$name] = $value;
    }

    public function offsetUnset($name) {
        unset($this->values[$name]);
    }
}