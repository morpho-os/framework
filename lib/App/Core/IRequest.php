<?php declare(strict_types=1);
namespace Morpho\App\Core;

interface IRequest extends IMessage {
    public function isHandled(bool $flag = null): bool;

    public function setHandler(array $handler): void;

    public function handler(): array;

    public function setModuleName(string $moduleName): void;

    public function moduleName(): ?string;

    public function setControllerName(string $controllerName): void;

    public function controllerName(): ?string;

    public function setActionName(string $actionName): void;

    public function actionName(): ?string;

    public function setResponse($response): void;

    public function response(): IResponse;

    /**
     * @param array|null $namesOrIndexes
     *     null | int[] | string[]
     * @return mixed
     */
    public function args($namesOrIndexes = null);

    /**
     * @param string|int $nameOrIndex
     * @return mixed
     */
    public function arg($nameOrIndex);
}
