<?php declare(strict_types=1);
namespace Morpho\Net;

interface IServer {
    /**
     * @return mixed
     */
    public function start();

    /**
     * @return mixed
     */
    public function stop();
}
