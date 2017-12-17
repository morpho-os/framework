<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

class Authority {
    /**
     * @var null|string
     */
    public $userInfo;
    /**
     * @var null|string
     */
    public $host;
    /**
     * @var int|null
     */
    public $port;

    public function __construct(string $authority = null) {
        if (null !== $authority) {
            $authority = UriParser::parseOnlyAuthority($authority);
            if (false !== $authority) {
                $this->userInfo = $authority->userInfo;
                $this->host = $authority->host;
                $this->port = $authority->port;
            }
        }
    }

    public function toString(bool $encode = true) {
        // @TODO: Handle $encode
        $authority = (string)$this->userInfo;
        if ('' !== $authority) {
            $authority .= '@';
        }
        $authority .= $this->host;
        if (null !== $this->port) {
            $authority .= ':' . $this->port;
        }
        return $authority;//$authority !== '' ? $authority : null;
    }
}