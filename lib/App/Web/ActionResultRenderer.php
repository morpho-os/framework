<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\IFn;

class ActionResultRenderer implements IFn {
    private $contentNegotiator;

    private $rendererFactory;

    public function __construct(callable $rendererFactory) {
        $this->rendererFactory = $rendererFactory;
    }

    public function __invoke($request) {
        $response = $request->response();
        if (!$response->isRedirect()) {
            $formats = $response->formats();
            if (count($formats)) {
                $currentFormat = null;
                if (count($formats) > 1) {
                    $contentNegotiator = $this->contentNegotiator();
                    $clientFormat = $contentNegotiator->__invoke($request);
                    $key = array_search($clientFormat, $formats, true);
                    if (false !== $key) {
                        $currentFormat = $formats[$key];
                    }
                } else {
                    $currentFormat = current($formats);
                }
                if ($currentFormat) {
                    $renderer = ($this->rendererFactory)($currentFormat);
                    $renderer->__invoke($request);
                }
            }
        }
    }

    public function setContentNegotiator($contentNegotiator) {
        $this->contentNegotiator = $contentNegotiator;
    }

    public function contentNegotiator() {
        if (null === $this->contentNegotiator) {
            $this->contentNegotiator = new ContentNegotiator();
        }
        return $this->contentNegotiator;
    }
}
