<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Uri;

use Morpho\Base\IFn;
use Morpho\Ioc\IServiceManager;

class UriChecker implements IFn {
    private $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param Uri $uri
     */
    public function __invoke($uri): bool {
        $site = $this->serviceManager['site'];
        return \in_array($uri->authority()->host(), $site->config()['hostNames']);
    }
}
