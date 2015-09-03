<?php
namespace Morpho\Web;

use Zend\Uri\Http as BaseUri;

class Uri extends BaseUri {
    private $basePath;

    public static function hasAuthority(string $uri): bool {
        return strlen($uri) > 2 && false !== strpos($uri, '//');
    }

    public function setBasePath(string $basePath) {
        $this->basePath = $basePath;
        return $this;
    }

    public function getBasePath(): string {
        return $this->basePath;
    }
}