<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Cli;

use function Morpho\Base\tail;
use Morpho\Web\Request as BaseRequest;

class Request extends BaseRequest {
    public function args(): array {
        $args = $_SERVER['argv'];
        return count($args) ? tail($args) : $args;
    }

    protected function newResponse() {
        return new Response();
    }
}