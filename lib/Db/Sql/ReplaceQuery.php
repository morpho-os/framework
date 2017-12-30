<?php declare(strict_types=1);
namespace Morpho\Db\Sql;

use Morpho\Base\NotImplementedException;

class ReplaceQuery extends Query {
    public function build(): array {
        throw new NotImplementedException();
    }
}