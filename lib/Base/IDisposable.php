<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * This interface is inspired by IDisposable from .NET but with adding extending the IFn. See [Overview](https://docs.microsoft.com/en-us/dotnet/standard/garbage-collection/unmanaged#in-this-section)
 */
interface IDisposable extends IFn {
    public function dispose(): void;
}