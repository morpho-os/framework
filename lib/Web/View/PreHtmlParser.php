<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

class PreHtmlParser extends HtmlParser {
    protected function tagA($tag) {
        if (isset($tag['href'][0]) && $tag['href'][0] === '/') {
            $tag['href'] = $this->prependUriWithBasePath($tag['href']);
            return $tag;
        }
    }
}