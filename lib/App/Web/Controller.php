<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\IResponse;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\App\Controller as BaseController;

abstract class Controller extends BaseController implements IHasServiceManager {
    protected IServiceManager $serviceManager;

    protected $request;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    protected function redirect(string $uri = null, int $statusCode = null) {
        $this->request->response()->redirect($uri, $statusCode);
        return $this->result();
    }

    protected function args($name = null, bool $trim = true) {
        return $this->request->args($name, $trim);
    }

    protected function query($name = null, bool $trim = true) {
        return $this->request->query($name, $trim);
    }

    protected function post($name, bool $trim = true) {
        return $this->request->post($name, $trim);
    }

    protected function jsConf(): \ArrayObject {
        if (!isset($this->request['jsConf'])) {
            $this->request['jsConf'] = new \ArrayObject();
        }
        return $this->request['jsConf'];
    }

    protected function messenger(): Messages\Messenger {
        return $this->serviceManager['messenger'];
    }

    protected function ok($data = true): ActionResult {
        return $this->result(['ok' => $data])
                    ->allowAjax(true)
                    ->setFormats(['json']);
    }

    protected function err($data = true): ActionResult {
        return $this->result(['err' => $data])
                    ->allowAjax(true)
                    ->setFormats('json');
    }

    protected function result($vars = []) {
        return (new ActionResult($vars))
            ->setMessenger($this->messenger());
    }

    protected function handleResult($actionResult) {
        if (!$actionResult instanceof ActionResult) {
            return $this->result($actionResult);
        }
        return $actionResult;
    }
}
