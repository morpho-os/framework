<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\IActionResult;

abstract class WebApiController extends Controller {
    protected function arrToActionResult(array $values): IActionResult {
        return $this->mkJson((array) $values);
    }
}
