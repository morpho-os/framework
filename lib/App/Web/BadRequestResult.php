<?php declare(strict_types=1);
namespace Morpho\App\Web;

/**
 * This class inspired by BadRequestResult from .NET
 */
class BadRequestResult extends StatusCodeResult {
    public function __construct() {
        parent::__construct(400);
    }
}
