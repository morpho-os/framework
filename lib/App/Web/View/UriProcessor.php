<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

class UriProcessor extends HtmlProcessor {
    protected function tagLink($tag) {
        return $this->prependTagAttrWithBasePath($tag, 'href');
    }

    protected function prependTagAttrWithBasePath(array $tag, string $attrName): array {
        if (isset($tag[self::SKIP_ATTR])) {
            return $tag;
        }
        if (isset($tag[$attrName]) && !str_starts_with($tag[$attrName], '<?')) {
            $tag[$attrName] = $this->request->prependUriWithBasePath($tag[$attrName])->toStr(null, false);
        }
        return $tag;
    }

    protected function tagA($tag) {
        return $this->prependTagAttrWithBasePath($tag, 'href');
    }

    protected function tagForm($tag) {
        return $this->prependTagAttrWithBasePath($tag, 'action');
    }

    protected function tagScript($tag) {
        return $this->prependTagAttrWithBasePath($tag, 'src');
    }
}
