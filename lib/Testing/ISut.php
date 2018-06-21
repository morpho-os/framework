<?php declare(strict_types=1);
namespace Morpho\Testing;

interface ISut {
    public function config(): \ArrayAccess;
}
