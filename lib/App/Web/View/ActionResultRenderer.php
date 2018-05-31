<?php declare(strict_types=1);
namespace Morpho\App\Web\View;

use Morpho\App\Web\Request;
use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;

class ActionResultRenderer implements IFn {
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    public function __invoke($request) {
        if (!$this->shouldRender($request)) {
            return;
        }
        $this->render($request);
    }

    public function shouldRender(Request $request): bool {
        $response = $request->response();
        /** @var \Morpho\App\Web\Response $response */
        return !$response->isRedirect();
    }

    protected function render(Request $request) {
        $renderer = $this->mkRenderer($request);
        $renderer->__invoke($request);
    }

    protected function mkRenderer(Request $request): IFn {
        $response = $request->response();
        if (empty($response['result'])) {
            throw new \UnexpectedValueException('Empty response result');
        }
        $actionResult = $response['result'];

        $serviceManager = $this->serviceManager;

        if ($actionResult instanceof View) {
            return new HtmlRenderer($serviceManager);
        }

        $contentNegotiator = $serviceManager['contentNegotiator'];
        $format = $contentNegotiator->__invoke($request);
        switch ($format) {
            case 'json';
                $renderer = new JsonRenderer();
                break;
            default:
            case 'html':
                $renderer = new HtmlRenderer($this->serviceManager);
                break;
            /* @TODO
             * case 'xml':
             * break;
             */
        }
        return $renderer;
    }
}
