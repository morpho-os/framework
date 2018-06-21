<?php declare(strict_types=1);
namespace Morpho\App\Web;

class ForbiddenResult extends StatusCodeResult {
    public function __construct() {
        parent::__construct(403);
    }
}
