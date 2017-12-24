<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

class Page extends View {
    private const DEFAULT_LAYOUT = 'index';

    /**
     * @var View
     */
    private $view;
    /**
     * @var View
     */
    private $layout;

    public function setView(View $view): string {
        $this->view = $view;
    }

    public function view(): View {
        if (null === $this->view) {
            $this->view = new View($this->name);
        }
        return $this->view;
    }

    public function setLayout(View $layout): void {
        $this->layout = $layout;
    }

    public function layout(): View {
        if (null === $this->layout) {
            $this->layout = new View(self::DEFAULT_LAYOUT);
        }
        return $this->layout;
    }
}