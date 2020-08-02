<?php declare(strict_types=1);
namespace Morpho\App;

interface IRequest extends IMessage {
    public function isHandled(bool $flag = null): bool;

    public function setHandler(array $handler): void;

    public function handler(): array;

    public function setResponse(IResponse $response): void;

    public function response(): IResponse;

    /**
     * @param array|null $namesOrIndexes
     *     null | int[] | string[]
     * @return mixed
     */
    public function args($namesOrIndexes = null);
}
