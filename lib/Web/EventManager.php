<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\Event;
use Morpho\Base\EventManager as BaseEventManager;
use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;
use Morpho\Web\View\HtmlRenderer;
use Morpho\Web\View\JsonRenderer;

class EventManager extends BaseEventManager {
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        $this->attachHandlers();
    }

    protected function attachHandlers() {
        $this->on('dispatchError', function (Event $event) {
            /** @var DispatchErrorHandler $dispatchErrorHandler */
            $dispatchErrorHandler = $this->serviceManager->get('dispatchErrorHandler');
            $config = $this->serviceManager->config()['dispatchErrorHandler'];
            $dispatchErrorHandler->throwErrors($config['throwErrors']);
            if (isset($config['handlers'])) {
                foreach ($config['handlers'] as $errorType => $handler) {
                    $dispatchErrorHandler->setHandler($errorType, $handler);
                }
            }
            /** @var Request $request */
            $request = $event->args['request'];
            $dispatchErrorHandler->handleError($event->args['exception'], $request);
            // $request->response()->isRendered(false);
        });

        $this->on('afterDispatch', function (Event $event) {
            /** @var Request $request */
            $request = $event->args['request'];

            if (!$this->shouldRender($request)) {
                return;
            }

            $serviceManager = $this->serviceManager;

            /** @var IFn $renderer */
            $format = $serviceManager->get('contentNegotiator')->__invoke($request);
            $renderer = $this->newRenderer($format, $serviceManager);
            $renderer->__invoke($request);
        });
    }

    public function shouldRender(Request $request): bool {
        if (!$request->isDispatched()) {
            return false;
        }
        if (!$request->isAjax()) {
            /** @var \Morpho\Web\Response $response */
            $response = $request->response();
            if ($response->isRedirect()) {
                return false;
            }
        }
        return isset($request['page']);
    }

    protected function newRenderer(string $rendererType, IServiceManager $serviceManager): IFn {
        switch ($rendererType) {
            default:
            case 'html':
                $renderer = new HtmlRenderer($serviceManager);
                break;
            case 'json';
                $renderer = new JsonRenderer();
                break;
            /* @TODO
            case 'xml':
            break;
             */
        }
        return $renderer;
    }
}