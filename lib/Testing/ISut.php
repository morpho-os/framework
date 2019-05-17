<?php declare(strict_types=1);
namespace Morpho\Testing;

interface ISut {
    public function baseDirPath(): string;

    public function config(): \ArrayAccess;
}
