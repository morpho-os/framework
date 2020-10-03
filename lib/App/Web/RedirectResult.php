<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\Web\Messages\Messenger;
use Morpho\App\Web\View\JsonResult;

class RedirectResult implements IActionResult {
    use TActionResult;

    public ?string $uri;

    public ?int $statusCode;

    protected Messenger $messenger;

    public function __construct(?string $uri, int $statusCode = null) {
        $this->uri = $uri;
        $this->statusCode = $statusCode;
    }

    public function setMessenger(Messenger $messenger) {
        $this->messenger = $messenger;
        return $this;
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

    public function __invoke($serviceManager) {
        $request = $serviceManager['request'];
        $response = $request->response();
        $response['result'] = $this;
        $redirectUri = null;
        $headers = $response->headers();
        if (null !== $this->uri) {
            $redirectUri = $this->uri;
        } elseif (!empty($headers['Location'])) {
            $redirectUri = $headers['Location'];
        }
        if (null === $redirectUri) {
            throw new \UnexpectedValueException();
        }
        if ($this->allowAjax() && $request->isAjax()) {
            $response->setStatusCode(200);

            if (isset($headers['Location'])) {
                unset($headers['Location']);
            }

            $actionResult = $response['result'] = new JsonResult([
                'redirect' => $redirectUri,
            ]);
            $actionResult->__invoke($serviceManager);
        } else {
            $response->setStatusCode(null !== $this->statusCode ? $this->statusCode : 301);
            $response->headers()['Location'] = $redirectUri;
        }
    }
}
