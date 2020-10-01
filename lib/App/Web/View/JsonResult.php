<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\App\IActionResult;
use Morpho\Base\Val;
use function Morpho\Base\{fromJson, toJson};

class JsonResult extends Val implements \JsonSerializable, IActionResult {
    /**
     * @return mixed
     */
    public function jsonSerialize() {
        return $this->val;
    }

    public function __invoke($serviceManager) {
        $request = $serviceManager['request'];
        $request->response()['result'] = $this;
        $renderer = $serviceManager['jsonRenderer'];
        $renderer->__invoke($request);
    }
}
