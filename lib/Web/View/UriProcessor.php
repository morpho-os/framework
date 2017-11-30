<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\startsWith;

class UriProcessor extends HtmlProcessor {
    protected function tagLink($tag) {
        return $this->prependUriInTag($tag, 'href');
    }

    protected function tagA($tag) {
        return $this->prependUriInTag($tag, 'href');
    }

    protected function tagForm($tag) {
        return $this->prependUriInTag($tag, 'action');
    }

    protected function tagScript($tag) {
        return $this->prependUriInTag($tag, 'src');
    }

    protected function prependUriInTag(array $tag, string $attrName): array {
        if (isset($tag[self::SKIP_ATTR])) {
            return $tag;
        }
        if (isset($tag[$attrName]) && $this->shouldPrependBasePath($tag[$attrName])) {
            $tag[$attrName] = $this->prependBasePath($tag[$attrName]);
        }
        return $tag;
    }

    protected function shouldPrependBasePath(string $uri): bool {
        return parent::shouldPrependBasePath($uri) && !startsWith($uri, '<?');
    }
}