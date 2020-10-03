<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

trait TActionResult {
    private bool $allowAjax = false;

    public function allowAjax(bool $flag = null): bool {
        if ($flag !== null) {
            $this->allowAjax = $flag;
        }
        return $this->allowAjax;
    }
}
