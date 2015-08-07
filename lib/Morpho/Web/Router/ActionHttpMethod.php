<?php
namespace Morpho\Web\Router;

abstract class ActionHttpMethod extends Object {
    /**
     * @Column(type="integer")
     * @Fk(table="action", column="id")
     */
    protected $actionId;

    /**
     * @Column(type="integer")
     * @Fk(table="controller", column="id")
     */
    protected $controllerId;
}