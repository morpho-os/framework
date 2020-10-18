<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Messages;

interface IHasMessenger {
    public function setMessenger(Messenger $messenger): self;

    public function addSuccessMessage(string $message, array $args = null): self;

    public function addInfoMessage(string $text, array $args = null): self;

    public function addWarningMessage(string $text, array $args = null): self;

    public function addErrorMessage(string $text, array $args = null): self;
}
