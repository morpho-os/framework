<?php declare(strict_types=1);
namespace Morpho\App\Web\View;

use Morpho\App\Web\Json;
use Morpho\App\Web\Request;
use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;

class ActionResultRenderer implements IFn {
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    public function __invoke($request): void {
        if (!$this->shouldRender($request)) {
            return;
        }
        $this->render($request);
    }

    public function shouldRender(Request $request): bool {
        $response = $request->response();
        /** @var \Morpho\App\Web\Response $response */
        return !$response->isRedirect() && isset($response['result']);
    }

    public function render(Request $request): void {
        $renderer = $this->chooseRenderer($request);
        /** @var IFn $renderer */
        $renderer->__invoke($request);
    }

    public function chooseRenderer(Request $request): callable {
        $response = $request->response();
        $actionResult = $response['result'] ?? null;
        if ($actionResult instanceof Json) {
            /* @TODO: if ($this->useContentNegotiation) $format = $negotiate(); mkRenderer($format);
            $contentNegotiator = $serviceManager['contentNegotiator'];
            $format = $contentNegotiator->__invoke($request);
            switch ($format) {
            */
            return new JsonRenderer();
        } elseif ($actionResult instanceof View) {
            return new HtmlRenderer($this->serviceManager);
        }
        throw new \UnexpectedValueException('Unexpected action result');
    }
}
