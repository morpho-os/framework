<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Di\ServiceManager;

abstract class HtmlParser extends HtmlSemiParser {
    protected $serviceManager;

    protected $filePath;

    private $request;

    public function __construct(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        parent::__construct();
    }

    public function setFilePath(string $filePath): void {
        $this->filePath = $filePath;
    }

    protected function prependUriWithBasePath(string $uri): string {
        if (substr($uri, 0, 2) === '<?') {
            return $uri;
        }
        return $this->request()
            ->uri()
            ->prependWithBasePath($uri);
    }

    protected function request() {
        if (null === $this->request) {
            $this->request = $this->serviceManager->get('request');
        }
        return $this->request;
    }
}