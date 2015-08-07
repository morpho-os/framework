<?php
namespace Morpho\Di;

interface IServiceManager {
    public function get($id);

    public function set($id, $service);
}
