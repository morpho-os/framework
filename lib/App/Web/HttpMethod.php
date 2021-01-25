<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\Base\Enum;

abstract class HttpMethod extends Enum {
    public const GET = 'GET';
    public const POST = 'POST';
    public const DELETE = 'DELETE';
    public const PATCH = 'PATCH';
    public const PUT = 'PUT';
    public const HEAD = 'HEAD';
    public const OPTIONS = 'OPTIONS';
}