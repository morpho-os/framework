<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\IFn;
use Negotiation\Negotiator;

class ContentNegotiator implements IFn {
    public const HTML_FORMAT = 'html';
    public const JSON_FORMAT = 'json';
    public const XML_FORMAT  = 'xml';
    public const ANY_FORMAT = 'any';

    protected $priorities = ['text/html', 'application/json'/*, 'application/xml;q=0.5'*/];

    protected $defaultFormat = self::HTML_FORMAT;

    /**
     * @param Request $request
     */
    public function __invoke($request): string {
        $headers = $request->headers();
        if (!$headers->offsetExists('Accept')) {
            return $this->defaultFormat;
        }
        $acceptHeaderStr = $headers->offsetGet('Accept');

        // @TODO: Replace with own implementation for speed.
        // Perform Media Type Negotiation
        $negotiator = new Negotiator();
        try {
            /** @var \Negotiation\Accept $mediaType */
            $mediaType = $negotiator->getBest($acceptHeaderStr, $this->priorities);
        } catch (\Negotiation\Exception\InvalidArgument $e) {
            return $this->defaultFormat;
        }
        if (!$mediaType) {
            return $this->defaultFormat;
        }
        return \strtolower($mediaType->getSubPart());
    }
}
