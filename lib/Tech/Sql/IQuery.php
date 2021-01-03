<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

use Stringable;

interface IQuery extends Stringable {
    /**
     * @param array|string $tableName
     */
    public function table($tableName): self;

    public function expr($expr): Expr;

    public function sql(): string;

    public function args(): array;

    /**
     * @param $condition
     * @param null|array|string|int $args If not null will be casted to array
     * @return $this
     */
    public function where($condition, $args = null): self;

    public function eval(): Result;

    /**
     * Builds (configures) query from the specification.
     * @param array $spec
     * @return $this
     */
    public function build(array $spec): self;
}
