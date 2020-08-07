<?php declare(strict_types=1);
namespace Morpho\App\Cli;

use Morpho\App\ActionMetaProvider as BaseActionMetaProvider;

class ActionMetaProvider extends BaseActionMetaProvider {
    protected function baseControllerClasses(): array {
        return [Controller::class];
    }
}