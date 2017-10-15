<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);

namespace Morpho\Web\View;

use Morpho\Base\Event;

interface IHasTheme {
    public function setTheme(Theme $theme): void;

    public function theme(): Theme;

    public function render(Event $event): string;

    public function afterDispatch(Event $event): void;
}