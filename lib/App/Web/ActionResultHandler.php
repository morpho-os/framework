<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\Web\View\HtmlRenderer;
use Morpho\App\Web\View\JsonRenderer;
use Morpho\App\Web\View\ViewResult;
use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;

class ActionResultHandler implements IFn {
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param Request $request
     */
    public function __invoke($request): void {
        /** @var \Morpho\App\Web\Response $response */
        $response = $request->response();
        if ($response->isRedirect()) {
            if ($request->isAjax()) {
                $response['result'] = new JsonResult(['redirect' => $response->headers()->offsetGet('Location')]);
                unset($response->headers()['Location']);
                $response->setStatusCode(Response::OK_STATUS_CODE);
                $renderer = new JsonRenderer();
                $renderer->__invoke($request);
            }
        } elseif (isset($response['result'])) {
            $actionResult = $response['result'];
            if ($actionResult instanceof StatusCodeResult) {
                $handlerMap = $this->serviceManager->config()['actionResultHandler'];
                $handler = $handlerMap[$actionResult->statusCode];
                $request->setHandler($handler);
                $request->isHandled(false);
            } elseif ($actionResult instanceof ViewResult) {
                $renderer = new HtmlRenderer($this->serviceManager);
                $renderer->__invoke($request);
            } elseif ($actionResult instanceof JsonResult){
                $renderer = new JsonRenderer();
                $renderer->__invoke($request);
            }
            /* @TODO: if ($this->useContentNegotiation) $format = $negotiate(); mkRenderer($format);
            $contentNegotiator = $serviceManager['contentNegotiator'];
            $format = $contentNegotiator->__invoke($request);
            switch ($format) {
            */
        }
    }
}
