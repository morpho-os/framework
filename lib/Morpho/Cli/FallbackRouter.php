<?php
namespace Morpho\Cli;

class FallbackRouter {
    public function route($request) {
        // Format: Subject Verb Object
        /*
        $path = rtrim($request->getUri()->getPath(), '/');
        $parts = array_slice(array_filter(explode('/', $path)), 0, 9);
        if (isset($parts[0]) && $parts[0] == 'install') {
            $action = 'install';
        } else {
            $action = 'index';
        }
        */
        return new Route([
            'module' => 'Morpho/System',
            'controller' => 'Cli',
            'action' => 'index',
        ]);
    }
}
