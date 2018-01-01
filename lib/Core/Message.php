<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

abstract class Message implements IMessage {
    /**
     * @var ?ArrayObject
     */
    protected $params;

    /**
     * Sets internal params, should not contain user input.
     * @param \ArrayObject|array $params
     */
    public function setParams($params): void {
        if (is_array($params)) {
            $this->params = new \ArrayObject($params);
        } else {
            $this->params = $params;
        }
    }

    /**
     * Returns storage for internal params.
     */
    public function params(): \ArrayObject {
        if (null === $this->params) {
            $this->params = new \ArrayObject();
        }
        return $this->params;
    }
}