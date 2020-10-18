<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

abstract class Container implements IContainer {
    /**
     * @var mixed
     */
    protected $val;

    /**
     * @param mixed $value
     */
    public function __construct($val) {
        $this->val = $val;
    }

    /**
     * @return mixed
     */
    public function val() {
        return $this->val;
    }
}
