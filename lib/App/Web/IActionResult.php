<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\IActionResult as IBaseActionResult;

interface IActionResult extends IBaseActionResult {
    /**
     * @return bool|IActionResult Returns IActionResult only when $flag !== null, returns bool otherwise.
     */
    public function allowAjax(bool $flag = null);
}
