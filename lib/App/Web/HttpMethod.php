<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\Enum;

abstract class HttpMethod extends Enum {
    // See https://tools.ietf.org/html/rfc7231#section-4.3, https://tools.ietf.org/html/rfc5789
    public const GET = 'GET';
    public const POST = 'POST';
    public const DELETE = 'DELETE';
    public const PATCH = 'PATCH';
    public const PUT = 'PUT';
    public const HEAD = 'HEAD';
    //public const CONNECT = 'CONNECT';
    //public const OPTIONS = 'OPTIONS';
    //public const TRACE = 'TRACE';
}
