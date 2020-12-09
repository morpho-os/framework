<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Morpho\App\IHostNameValidator;
use RuntimeException;

class HostNameValidator implements IHostNameValidator {
    private array $allowedHostNames;
    private string $currentHostName;

    public function __construct(array $allowedHostNames, string $currentHostName) {
        $this->allowedHostNames = $allowedHostNames;
        $this->currentHostName = $currentHostName;
    }

    /**
     * @throws RuntimeException
     */
    public function throwInvalidSiteError(): void {
        throw new RuntimeException('Invalid site');
    }

    /**
     * @return string|false
     */
    public function currentHostName() {
        return $this->currentHostName;
    }

    public function isValid($hostName): bool {
        return in_array($hostName, $this->allowedHostNames, true);
    }
}
