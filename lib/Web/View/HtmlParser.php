<?php
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

    protected function escapeHtml($var): string {
        return htmlspecialchars($var, ENT_QUOTES);
    }
}