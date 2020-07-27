<?php declare(strict_types=1);
namespace Morpho\App\Cli;

use Morpho\App\SiteFactory as BaseSiteFactory;

class SiteFactory extends BaseSiteFactory {
    /**
     * @throws \RuntimeException
     */
    protected function throwInvalidSiteError(): void {
        throw new \RuntimeException('Invalid site');
    }

    /**
     * @return string|false
     */
    protected function currentHostName() {
        return 'localhost';
    }

    protected function isAllowedHostName(string $hostName): bool {
        return $hostName === 'localhost';
    }
}
