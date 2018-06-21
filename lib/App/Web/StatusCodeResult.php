<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\IActionResult;

/**
 * This class inspired by NotFoundResult from .NET
 */
class StatusCodeResult implements IActionResult {
    /**
     * @var int
     */
    public $statusCode;

    public function __construct(int $statusCode) {
        $this->statusCode = $statusCode;
    }
}
