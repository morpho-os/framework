<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Fs\Path;

class UriProcessor extends HtmlProcessor {
    protected function tagLink($tag) {
        return $this->prependBasePath($tag, 'href');
    }

    protected function tagA($tag) {
        return $this->prependBasePath($tag, 'href');
    }

    protected function tagForm($tag) {
        return $this->prependBasePath($tag, 'action');
    }

    protected function tagScript($tag) {
        return $this->prependBasePath($tag, 'src');
    }

    protected function prependBasePath(array $tag, string $attrName): array {
        if (isset($tag[self::SKIP_ATTR])) {
            return $tag;
        }
        if (isset($tag[$attrName]) && !str_starts_with($tag[$attrName], '<?')) {
            $uriStr = $tag[$attrName];
            if (isset($uriStr[0]) && $uriStr[0] === '/') {
                if (isset($uriStr[1]) && $uriStr[1] === '/') {
                    // URI starts with //
                    return $tag;
                }
                $basePath = $this->request()->uri()->path()->basePath();
                $tag[$attrName] = Path::combine($basePath, \substr($uriStr, 1));
            }
        }
        return $tag;
    }
}
