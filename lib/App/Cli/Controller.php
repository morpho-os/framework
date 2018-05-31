<?php declare(strict_types=1);
namespace Morpho\App\Cli;

use Morpho\App\Core\Controller as BaseController;
use Morpho\App\Core\IResponse;

class Controller extends BaseController {
    /**
     * @param mixed $actionResult
     */
    protected function handleActionResult($actionResult): IResponse {
        $response = $this->request->response();
        $actionResult = (string) $actionResult;
        $response['result'] = $actionResult;
        return $response;
    }

    protected function mkNotFoundResponse(string $actionName): IResponse {
        $response = new Response();
        $response->setBody('Not found');
        return $response;
    }
}
