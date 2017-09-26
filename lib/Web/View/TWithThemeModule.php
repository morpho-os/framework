<?php
//declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Web\View;

trait TWithThemeModule {
    /**
     * @var ?Theme
     */
    protected $theme;

    public function setTheme(Theme $theme): void {
        $this->theme = $theme;
    }

    public function theme(): Theme {
        if (null === $this->theme) {
            $theme = new Theme();
            $theme->setServiceManager($this->serviceManager);
            $this->theme = $theme;
        }
        return $this->theme;
    }

    /**
     * @Listen render -9999
     */
    public function render($event): string {
        /** @var \Morpho\Core\View $view */
        $view = $event[1]['view'];
        return $this->theme()->renderView($view);
    }

    /**
     * @Listen afterDispatch -9999
     */
    public function afterDispatch($event): void {
        $request = $event[1]['request'];
        $this->theme->renderLayout($request);
    }
}