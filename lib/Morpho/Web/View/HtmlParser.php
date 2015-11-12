<?php
namespace Morpho\Web\View;

use Morpho\Web\ServiceManager;

abstract class HtmlParser extends HtmlSemiParser {
    protected $serviceManager;

    protected $filePath;

    public function __construct(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        parent::__construct();
    }

    public function setFilePath(string $filePath) {
        $this->filePath = $filePath;
    }

    protected function prependUriWithBasePath(string $uri): string {
        return $this->serviceManager->get('request')
            ->currentUri()
            ->prependWithBasePath($uri);
    }
}

