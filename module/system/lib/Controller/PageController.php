<?php
declare(strict_types=1);
namespace Morpho\System\Controller;

use Morpho\System\Module;
use Morpho\Web\Controller;
use Morpho\Web\Request;
use Morpho\Web\Uri;

class PageController extends Controller {
    /**
     * @POST|GET
     */
    public function indexAction() {
        if ($this->isPostMethod()) {
            $uris = $this->post([Request::HOME_HANDLER, Request::NOT_FOUND_ERROR_HANDLER, Request::ACCESS_DENIED_ERROR_HANDLER, Request::UNCAUGHT_ERROR_HANDLER]);
            $router = $this->serviceManager->get('router');
            $settingsManager = $this->serviceManager->get('settingsManager');
            $redirect = true;
            foreach ($uris as $handlerName => $uri) {
                if (!empty($uri)) {
                    $request = (new Request())
                        ->setMethod(Request::GET_METHOD)
                        ->setUri(new Uri($uri));
                    $router->route($request);
                    $handler = $request->handler();
                    $valid = true;
                    foreach ($handler as $part) {
                        if (empty($part)) {
                            $valid = false;
                            $redirect = false;
                        }
                    }
                    if (!$valid) {
                        $this->addErrorMessage("The handler for the URI '{0}' was not found", [$uri]);
                    } else {
                        $settingsManager->set($handlerName, ['handler' => $request->handler(), 'uri' => $request->uriPath()], $this->parentByType('Module')->name());
                    }
                }
            }
            if ($redirect) {
                $this->addSuccessMessage("Settings have been saved successfully");
                $this->redirectToSelf();
                return;
            } else {
                return $uris;
            }
        } else {
            // @TODO: Add ServiceManager API to fetch multiple settings.
            $map = $this->db()->select("s.name, s.value FROM setting AS s
                INNER JOIN module AS m 
            ON s.moduleId = m.id
            WHERE s.name LIKE '%Handler' AND m.name = ?", [Module::NAME])->map();
            $handlersToUri = [
                Request::HOME_HANDLER => null,
                Request::NOT_FOUND_ERROR_HANDLER => null,
                Request::ACCESS_DENIED_ERROR_HANDLER => null,
                Request::UNCAUGHT_ERROR_HANDLER => null,
            ];
            foreach ($map as $handlerName => $value) {
                ['uri' => $uri] = unserialize($value);
                $handlersToUri[$handlerName] = $uri;
            }
            return $handlersToUri;
        }
    }
}