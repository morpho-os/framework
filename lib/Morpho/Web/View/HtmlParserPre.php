<?php
namespace Morpho\Web\View;

class HtmlParserPre extends HtmlParser {
    protected function tagA($tag) {
        if (isset($tag['href'][0]) && $tag['href'][0] === '/') {
            $tag['href'] = $this->prependUriWithBasePath($tag['href']);
            return $tag;
        }
    }
}