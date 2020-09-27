<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\IActionResult;
use Morpho\Base\Val;
use function Morpho\Base\{fromJson, toJson};

class JsonResult extends Val implements \JsonSerializable, IActionResult {
    public const FORMAT = 'json';

    /*
    public function __serialize(): array {
        $val = $this->
        return [$this->val()];
        return ["prop_a" => $this->prop_a];
    }
    public function __unserialize(array $data) {
        $this->prop_a = $data["prop_a"];
    }
     */

    /**
     * @return mixed
     */
    public function jsonSerialize() {
        return $this->val;
    }
}
