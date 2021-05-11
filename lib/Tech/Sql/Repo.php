<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

abstract class Repo {
    protected IDbClient $db;

    public function __construct(IDbClient $db) {
        $this->db = $db;
    }
}