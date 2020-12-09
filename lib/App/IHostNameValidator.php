<?php declare(strict_types=1);
namespace Morpho\App;

interface IHostNameValidator {
    /**
     * @throws \RuntimeException
     */
    public function throwInvalidSiteError(): void;

        /**
     * @return string|false
     */
    public function currentHostName();

    public function isValid($hostName): bool;
}