<?php declare(strict_types=1);
namespace Morpho\Base;

/**
 * This interface is inspired by IDisposable from .NET but with adding extending the IFn.
 */
interface IDisposable extends IFn {
    public function dispose(): void;
}
