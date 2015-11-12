<?php
namespace Morpho\Web\View;

class HtmlParserPre extends HtmlParser {
    protected function tagA($tag) {
        if (isset($tag['href'][0]) && $tag['href'][0] === '/') {
            $tag['href'] = $this->prependUriWithBasePath($tag['href']);
            return $tag;
        }
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
}