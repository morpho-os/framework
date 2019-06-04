<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\IActionResult;

abstract class WebApiController extends Controller {
    protected function mkDefaultResult(array $values = null): IActionResult {
        return $this->mkJsonResult((array) $values);
    }
}
