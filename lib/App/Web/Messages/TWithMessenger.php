<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Messages;

trait TWithMessenger {
    protected Messenger $messenger;

    public function setMessenger(Messenger $messenger) {
        $this->messenger = $messenger;
        return $this;
    }

    public function withSuccessMessage(string $message, array $args = null) {
        $this->messenger->addSuccessMessage($message, $args);
        return $this;
    }

    public function withInfoMessage(string $text, array $args = null) {
        $this->messenger->addInfoMessage($text, $args);
        return $this;
    }

    public function withWarningMessage(string $text, array $args = null) {
        $this->messenger->addWarningMessage($text, $args);
        return $this;
    }

    public function withErrorMessage(string $text, array $args = null) {
        $this->messenger->addErrorMessage($text, $args);
        return $this;
    }
}
