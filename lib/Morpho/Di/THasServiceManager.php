<?php
namespace Morpho\Di;

trait THasServiceManager {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}