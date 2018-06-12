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

    public static function check(array $defaultConfig, ?array $config): array {
        if (null === $config || \count($config) === 0) {
            return $defaultConfig;
        }
        $diff = \array_diff_key($config, \array_flip(\array_keys($defaultConfig)));
        if (\count($diff)) {
            throw new InvalidConfigException($diff);
        }
        return \array_merge($defaultConfig, $config);
    }

    /**
     * @TODO support $config: \Config|ArrayObject
     */
    public function merge(array $config, bool $recursive = true): self {
        if ($recursive) {
            $this->exchangeArray(ArrayUtils::merge($this->getArrayCopy(), $config));
        } else {
            $this->exchangeArray(\array_merge($this->getArrayCopy(), $config));
        }
        return $this;
    }
}
