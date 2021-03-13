<?php declare(strict_types=1);
namespace Morpho\App;

interface IRequest extends IMessage {
    public function isHandled(bool $flag = null): bool;

    public function setHandler(array $handler): void;

    public function handler(): array;

    public function setResponse(IResponse $response): void;

    public function response(): IResponse;

    public function args(string|array|null $namesOrIndexes = null): mixed;
}
