<?php
declare(strict_types=1);
namespace Morpho\Localhost;

use Morpho\Web\ISite;
use Morpho\Web\TSiteWrapper;
use Morpho\Web\Module as BaseModule;

class Module extends BaseModule implements ISite {
    use TSiteWrapper;

    //private const NAME = 'morpho-os/localhost';
}