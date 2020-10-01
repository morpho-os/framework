<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\IActionResult;
use Morpho\Base\Val;
use Morpho\App\Web\View\JsonResult;

class StatusCodeResult extends Val implements IActionResult {
    public function __invoke($serviceManager) {
        $request = $serviceManager['request'];
        $response = $request->response();
        $response['result'] = $this;
        $response->setStatusCode($this->val());
        /*
        if ($request->isAjax()) {
            $actionResult = new JsonResult(['err' => $response->statusCodeToReason($this->val())]);
            $actionResult->__invoke($serviceManager);
        } else {
         */
            $handlerMap = $serviceManager->conf()['actionResultHandler'];
            $handler = $handlerMap[$this->val()];
            $request->setHandler($handler);
            $request->isHandled(false);
        //}
    }
}
