<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

interface IResponse {
    public function setBody(string $body): void;

    public function body(): string;

    public function isBodyEmpty(): bool;

    public function send(): void;

    public function meta(): \ArrayObject;
}