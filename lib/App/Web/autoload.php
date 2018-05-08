<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

// Below are some constants for the web-application, see the Morpho/Core/autoload.php for the Core-level constants.

use Morpho\Fs\Path;
use Morpho\App\Web\Uri\Uri;
const CSS_DIR_NAME = 'css';
const DOMAIN_DIR_NAME = 'domain';
const FONT_DIR_NAME = 'font';
const IMG_DIR_NAME = 'img';
const JS_DIR_NAME = 'js';
const PUBLIC_DIR_NAME = 'public';
const SCRIPT_DIR_NAME = 'script';
const UPLOAD_DIR_NAME = 'upload';

/**
 * @param string|callable $provideBasePath
 * @param string|Uri $uri
 */
function prependBasePath($provideBasePath, $uri): Uri {
    if (\is_string($uri)) {
        $uri = new Uri($uri);
    }
    if ($uri->authority()->isNull() && $uri->scheme() === '') {
        $path = $uri->path();
        if (!$path->isRel()) {
            $uriStr = Path::combine($provideBasePath(), $uri->toStr(false));
            return new Uri($uriStr);
        }
    }
    return $uri;
}
