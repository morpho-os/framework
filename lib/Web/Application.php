<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\IBootstrapFactory;
use Morpho\Core\Application as BaseApplication;
use Morpho\Web\View\Html;

class Application extends BaseApplication {
    protected function init(): void {
        Environment::init();
        $serviceManager = $this->serviceManager();
        $serviceManager->get('errorHandler')->register();
    }

    protected function applyIniSettings(array $iniSettings, $parentName = null): void {
        parent::applyIniSettings($iniSettings, $parentName);
        if (null === $parentName) {
            if (!empty($_SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
                ini_set('cookie_secure', '1');
            }
        }
    }

    protected function showError(\Throwable $e): void {
        $statusLine = null;
        if ($e instanceof NotFoundException) {
            $statusLine = Environment::httpVersion() . ' 404 Not Found';
            $message = "The requested page was not found";
        } elseif ($e instanceof AccessDeniedException) {
            $statusLine = Environment::httpVersion() . ' 403 Forbidden';
            $message = "You don't have access to the requested resource";
        } elseif ($e instanceof BadRequestException) {
            $statusLine = Environment::httpVersion() . ' 400 Bad Request';
            $message = "Bad request, please contact site's support";
        } else {
            $statusLine = Environment::httpVersion() . ' 500 Internal Server Error';
            $message = "Unable to handle the request. Please contact site's support and try to return to this page again later";
        }
        if (!headers_sent()) {
            // @TODO: Use http_response_code()?
            header($statusLine);
        }
        for ($i = 0, $n = ob_get_level(); $i < $n; $i++) {
            //ob_end_flush();
            \ob_end_clean();
        }
        echo Html::encode($message) . '.';
    }

    protected function newBootstrapFactory(): IBootstrapFactory {
        return new BootstrapFactory();
    }
}
