<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use ArrayObject;
use Morpho\Base\Result;
use Morpho\Base\Ok;
use Morpho\Base\Err;
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

    protected function jsConf(): ArrayObject {
        if (!isset($this->request['jsConf'])) {
            $this->request['jsConf'] = new ArrayObject();
        }
        return $this->request['jsConf'];
    }

    protected function messenger(): Messages\Messenger {
        return $this->serviceManager['messenger'];
    }

    protected function ok($data = true): Result {
        return new Ok($data);
    }

    protected function err($data = true): Result {
        return new Err($data);
    }

    protected function handleResult($actionResult) {
        if ($actionResult instanceof Result) {
            if ($actionResult instanceof Ok) {
                $actionResult = ['ok' => $actionResult->val()];
            } elseif ($actionResult instanceof Err) {
                $actionResult = ['err' => $actionResult->val()];
            }
            $response = $this->request->response();
            $response->allowAjax(true)
                ->setFormats([ContentFormat::JSON]);
        }
        return $actionResult;
    }
}
