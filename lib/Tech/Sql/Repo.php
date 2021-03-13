<?php declare(strict_types=1);
namespace Morpho\Tech\Sql;

abstract class Repo {
    protected IDbClient $db;

    public function __construct(IDbClient $db) {
        $this->db = $db;
    }
}