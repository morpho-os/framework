<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use Zend\Stdlib\ArrayUtils;

class Config extends \ArrayObject {
    protected $default;

    public function __construct($values = null) {
        if (null === $values) {
            if (null !== $this->default) {
                parent::__construct($this->default);
            } else {
                parent::__construct([]);
            }
        } else {
            parent::__construct($values);
        }
    }

    /**
     * @TODO support $config: \Config|ArrayObject
     */
    public function merge(array $config, bool $recursive = true): self {
        if ($recursive) {
            $this->exchangeArray(ArrayUtils::merge($this->getArrayCopy(), $config));
        } else {
            $this->exchangeArray(array_merge($this->getArrayCopy(), $config));
        }
        return $this;
    }
}