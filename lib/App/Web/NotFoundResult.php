<?php declare(strict_types=1);
namespace Morpho\App\Web;

/**
 * This class inspired by NotFoundResult from .NET
 */
class NotFoundResult extends StatusCodeResult {
    public function __construct() {
        parent::__construct(404);
    }
}
