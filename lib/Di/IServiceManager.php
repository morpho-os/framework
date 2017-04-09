<?php
namespace Morpho\Di;

interface IServiceManager {
    public function get(string $id);

    public function set(string $id, $service);

    //public function setFactory(string $serviceId, $factory);
}
