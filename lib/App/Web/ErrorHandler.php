<?php declare(strict_types=1);
namespace Morpho\App\Web;

use function Morpho\Base\e;
use Morpho\App\Web\View\Html;
use Morpho\Error\ErrorHandler as BaseErrorHandler;

class ErrorHandler extends BaseErrorHandler {
    public function __construct(iterable $listeners = null) {
        parent::__construct($listeners);
        $this->listeners()->append($this->mkListener());
    }

    public function handleException(\Throwable $e): void {
        parent::handleException($e);
    }

    protected function mkListener(): \Closure {
        return function (\Throwable $e) {
            $statusLine = null;
/*            if ($e instanceof NotFoundException) {
                $statusLine = Environment::httpVersion() . ' 404 Not Found';
                $message = "The requested resource was not found";
            } elseif ($e instanceof AccessDeniedException) {
                $statusLine = Environment::httpVersion() . ' 403 Forbidden';
                $message = "You don't have access to the requested resource";
            } else*/if ($e instanceof BadRequestException) {
                $statusLine = Env::httpVersion() . ' 400 Bad Request';
                $message = "Bad request, please contact site's support";
            } else {
                $statusLine = Env::httpVersion() . ' 500 Internal Server Error';
                $message = "Unable to handle the request. Please contact site's support and try to return to this page again later";
            }
            if (!\headers_sent()) {
                // @TODO: Use http_response_code()?
                \header($statusLine);
            }
            for ($i = 0, $n = \ob_get_level(); $i < $n; $i++) {
                //ob_end_flush();
                \ob_end_clean();
            }
            echo e($message) . '.';
        };
    }
}
