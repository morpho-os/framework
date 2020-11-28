<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Base\NotImplementedException;
use Morpho\Tech\Sql\Expr;
use Morpho\Tech\Sql\IDbClient;
use Morpho\Tech\Sql\Result;
use UnexpectedValueException;

trait TQuery {
    protected IDbClient $db;

    protected array $tables = [];

    protected array $where = [];

    protected array $args = [];

    public function __construct(IDbClient $db, array $spec = null) {
        $this->db = $db;
        if (null !== $spec) {
            $this->build($spec);
        }
    }

    /**
     * @param array|string $tableName
     */
    public function table($tableName): self {
        $this->tables[] = $tableName;
        return $this;
    }

    public function expr($expr): Expr {
        return $this->db->expr($expr);
    }

    public function where($condition, array $args = null): self {
        if (null === $args) {
            if (!is_array($condition)) {
                throw new UnexpectedValueException();
            }
            // If args are not specified then $condition contains arguments
            $this->where[] = implode(' AND ', $this->db->nameValArgs($condition));
            $this->args = array_merge($this->args, array_values($condition));
        } else {
            $this->where[] = $condition;
            $this->args = array_merge($this->args, $args);
        }
        return $this;
    }

    public function eval(): Result {
        return $this->db->eval($this->sql(), $this->args());
    }

    public function build(array $spec): self {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * This method is for debugging only and should not be run on the server as SQL injection is possible, use eval() instead.
     * It is useful to see how the full SQL query will look like with placeholders substituded with arguments.
     * @return string
     */
    public function __toString(): string {
        $sql = $this->sql();
        $args = $this->args();
        if ($args) {
            // todo: replace named args like foo = :foo AND bar = :bar
            $sql = preg_replace_callback('~\?~s', function ($match) use (&$args): string {
                $val = array_shift($args);
                if (is_string($val)) {
                    return $this->db->pdo()->quote($val);
                }
                throw new NotImplementedException();
            }, $sql);
        }
        return $sql;
    }

    abstract public function sql(): string;

    public function args(): array {
        return $this->args;
    }

    protected function tableRefSql(): string {
        // https://mariadb.com/kb/en/join-syntax/
        $sql = [];
        foreach ($this->tables as $from) {
            $sql[] = $from instanceof Expr ? $from->val() : $this->db->quoteIdentifier($from);
        }
        return implode(', ', $sql);
    }

    protected function whereClauseSql(): ?string {
        if ($this->where) {
            return 'WHERE ' . implode(' AND ', $this->where);
        }
        return null;
    }
}