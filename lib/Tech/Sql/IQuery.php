<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

use Stringable;

interface IQuery extends Stringable {
    public function expr($expr): Expr;

    public function sql(): string;

    public function args(): array;

    public function eval(): Result;

    /**
     * Builds (configures) query from the specification.
     * @param array $spec
     * @return $this
     */
    public function build(array $spec): self;
}
