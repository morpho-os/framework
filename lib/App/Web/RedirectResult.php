<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\IActionResult;
use Morpho\App\Web\Messages\Messenger;

class RedirectResult implements IActionResult {
    /**
     * @var null|string
     */
    public $uri;

    /**
     * @var int|null
     */
    public $statusCode;

    protected $messenger;

    public function __construct(?string $uri, int $statusCode = null, Messenger $messenger) {
        $this->uri = $uri;
        $this->statusCode = $statusCode;
        $this->messenger = $messenger;
    }

    public function withSuccessMessage(string $message, array $args = null) {
        $this->messenger->addSuccessMessage($message, $args);
        return $this;
    }

    public function withInfoMessage(string $text, array $args = null) {
        $this->messenger->addInfoMessage($text, $args);
        return $this;
    }

    public function withWarningMessage(string $text, array $args = null) {
        $this->messenger->addWarningMessage($text, $args);
        return $this;
    }

    public function withErrorMessage(string $text, array $args = null) {
        $this->messenger->addErrorMessage($text, $args);
        return $this;
    }
}
