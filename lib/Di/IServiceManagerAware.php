<?php
namespace Morpho\Di;

interface IServiceManagerAware {
    public function setServiceManager(IServiceManager $serviceManager);
}
