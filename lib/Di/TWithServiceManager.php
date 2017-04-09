<?php
namespace Morpho\Di;

trait TWithServiceManager {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}