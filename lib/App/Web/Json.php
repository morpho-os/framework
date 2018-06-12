<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\IActionResult;
use function Morpho\Base\{fromJson, toJson};

class Json implements \JsonSerializable, \Serializable, IActionResult {
    public const FORMAT = 'json';
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function value() {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize() {
        return $this->value;
    }

    public function serialize(): ?string {
        return toJson($this->value);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void {
        $this->value = fromJson($serialized);
    }
}
