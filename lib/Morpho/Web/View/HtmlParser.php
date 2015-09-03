<?php
namespace Morpho\Web\View;

use Morpho\Web\ServiceManager;

class HtmlParser extends HtmlSemiParser {
    protected $serviceManager;

    protected $ignoredTags = [];

    protected $filePath;

    protected $scripts = [];

    private $scriptIndex = 0;

    public function __construct(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        parent::__construct();
    }

    public function setFilePath($filePath) {
        $this->filePath = $filePath;
    }

    protected function tagA($tag) {
        if (isset($tag['href'][0]) && $tag['href'][0] === '/') {
            $tag['href'] = $this->prependUriWithBasePath($tag['href']);
            return $tag;
        }
    }

    protected function containerBody($tag) {
        $childScriptsHtml = $this->renderScripts();
        $tag['_text'] = $this->filter($tag['_text'])
            . $this->renderScripts()
            . "\n" . $childScriptsHtml;
        return $tag;
    }

    protected function containerScript($tag) {
        if (isset($tag['skip'])) {
            unset($tag['skip']);
            return $tag;
        }
        if ((isset($tag['type']) && $tag['type'] == 'text/javascript') || !isset($tag['type'])) {
            if (isset($tag['index'])) {
                $index = intval($tag['index']);
                unset($tag['index']);
                $this->scripts[$index] = $tag;
            } else {
                $this->scripts[$this->scriptIndex++] = $tag;
            }
        }
        return false;  // remove the original tag, we will add it later.
    }

    protected function tagForm($tag) {
        if (isset($tag['action'])) {
            $uri = $tag['action'];
            if (isset($uri[0]) && $uri[0] == '/') {
                $tag['action'] = $this->prependUriWithBasePath($uri);
            }
        }
        return $tag;
    }

    protected function renderScripts() {
        $html = [];
        $scripts = $this->scripts;
        ksort($scripts, SORT_NUMERIC);
        foreach ($scripts as $scriptTag) {
            if (isset($scriptTag['src'])) {
                $scriptTag['src'] = $this->prependUriWithBasePath($scriptTag['src']);
            }
            $html[] = $this->makeTag($scriptTag);
        }
        $this->scripts = [];
        return implode("\n", $html);
    }

    protected function prependUriWithBasePath(string $uri): string {
        return $this->serviceManager->get('request')
            ->prependUriWithBasePath($uri);
    }
}
