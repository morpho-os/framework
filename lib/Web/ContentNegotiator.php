<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\IFn;
use Negotiation\Negotiator;

class ContentNegotiator implements IFn {
    /**
     * @param Request $request
     * @return string|false
     */
    public function __invoke($request) {
        if ($request->isAjax()) {
            return new View\JsonRenderer();
        }
        $headers = $request->headers();
        if (!$headers->offsetExists('Accept')) {
            return false;
        }
        $acceptHeaderStr = $headers->offsetGet('Accept');

        // @TODO: Replace with own implementation for speed.
        // Perform Media Type Negotiation
        $negotiator = new Negotiator();
        $priorities = ['text/html; charset=UTF-8', 'application/json'];/* @TODO:, 'application/xml;q=0.5'];*/
        /** @var \Negotiation\Accept $mediaType */
        $mediaType = $negotiator->getBest($acceptHeaderStr, $priorities);
        if (!$mediaType) {
            return false;
        }
        return strtolower($mediaType->getSubPart());
    }
}