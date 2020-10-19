<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\App\Web\Messages\Messenger;
use function Morpho\Base\{
    dasherize, format
};
use Morpho\Ioc\{
    IServiceManager, IHasServiceManager
};

class MessengerPlugin extends Plugin implements \Countable, IHasServiceManager {
    private $serviceManager;

    public function count(): int {
        return $this->messenger()->count();
    }

    public function renderPageMessages(): string {
        $html = '';
        $messenger = $this->messenger();
        if ($this->count()) {
            $renderedMessages = [];
            foreach ($messenger as $type => $messages) {
                $renderedMessages[] = $this->renderMessagesOfType($messages, $type);
            }
            $html = $this->formatHtmlContainer($renderedMessages);
        }
        $messenger->clearMessages();
        return $html;
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    protected function formatHtmlContainer(array $renderedMessages): string {
        return '<div id="page-messages">' . \implode("\n", $renderedMessages) . '</div>';
    }

    protected function renderMessagesOfType(iterable $messages, string $type) {
        $renderedMessages = [];
        $cssClass = $this->messageTypeToCssClass($type);
        foreach ($messages as $message) {
            $renderedMessages[] = $this->renderMessageOfType($message, $type);
        }
        return '<div class="messages ' . $this->messageTypeToCssClass($type) . '">'
            . \implode("\n", $renderedMessages)
            . '</div>';
    }

    protected function renderMessageOfType(array $message, string $type): string {
        $text = format(
            \nl2br(PhpTemplateEngine::e($message['text'])),
            $message['args'],
            function ($arg) { return $arg; }
        );
        $cssClass = $this->messageTypeToCssClass($type);
        return '<div class="alert alert-' . $cssClass . ' alert-dismissible fade show" role="alert">' . $text . ' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    }

    protected function messenger(): Messenger {
        return $this->serviceManager['messenger'];
    }

    protected function messageTypeToCssClass(string $type): string {
        $type2CssClass = [
            \Morpho\App\Web\Messages\Messenger::ERROR => 'danger',
            \Morpho\App\Web\Messages\Messenger::INFO => 'info',
            \Morpho\App\Web\Messages\Messenger::SUCCESS => 'success',
            \Morpho\App\Web\Messages\Messenger::WARNING => 'warning',
        ];
        return $type2CssClass[$type] ?? dasherize($type);
    }
}
