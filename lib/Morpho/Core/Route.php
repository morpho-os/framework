<?php
namespace Morpho\Core;

use Morpho\Base\Object;

/**
 * @Table(name="route")
 */
abstract class Route extends Object {
    /**
     * @Column(type="text", length=65535, nullable="true")
     */
    protected $params;
}