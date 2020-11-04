<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

class ServerCollation extends Collation {
    /**
     * @var bool
     */
    private $isDefault;

    public function __construct(string $name, string $charsetName, bool $isDefault) {
        parent::__construct($name, $charsetName);
        $this->isDefault = $isDefault;
    }

    public function isDefault(): bool {
        return $this->isDefault;
    }
}