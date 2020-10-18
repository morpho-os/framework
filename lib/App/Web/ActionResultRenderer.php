<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\IFn;
use Morpho\App\IActionResult;
use Morpho\Base\NotImplementedException;
use Morpho\App\Web\View\HtmlRenderer;

class ActionResultRenderer implements IFn {
    private $contentNegotiator;

    private $rendererFactory;

    public function __construct(callable $rendererFactory) {
        $this->rendererFactory = $rendererFactory;
    }

    public function __invoke($request) {
        $response = $request->response();
        if (!$response->isRedirect()) {
            $result = $response['result'];
            $formats = $result->formats();
            if (count($formats)) {
                $format = null;
                if (count($formats) > 1) {
                    $contentNegotiator = $this->contentNegotiator();
                    $clientFormat = $contentNegotiator->__invoke($request);
                    $key = array_search($clientFormat, $formats, true);
                    if (false !== $key) {
                        $format = $formats[$key];
                    }
                } else {
                    $format = current($formats);
                }
                if ($format) {
                    $renderer = ($this->rendererFactory)($format);
                    $renderer->__invoke($result);
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
