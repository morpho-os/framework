<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

class Query extends \ArrayObject {
    /**
     * @param string|array|object $queryStrOrQueryArgs
     */
    public function __construct($queryStrOrQueryArgs = []) {
        if (is_string($queryStrOrQueryArgs)) {
            $query = UriParser::parseOnlyQuery($queryStrOrQueryArgs);
            if (false === $query) {
                $query = [];
            }
            $this->exchangeArray($query);
        } else {
            parent::__construct($queryStrOrQueryArgs);
        }
    }

    public function isEmpty(): bool {
        foreach ($this as $name => $value) {
            return false;
        }
        return true;
    }

    public function toString(bool $encode = true): string {
        $queryStr = '';
        foreach ($this as $name => $value) {
            $queryStr .= '&' . ($encode ? rawurlencode($name) : $name);
            if (null !== $value) {
                $queryStr .= '=' . ($encode ? rawurlencode($value) : $value);
            }
        }
        return ltrim($queryStr, '&');
    }
}