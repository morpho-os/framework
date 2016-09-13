/// <reference path="widget" />

export const enum MessageType {
    Error = 1,
    Warning = 2,
    Info = 4,
    Debug = 8,
    All = Error | Warning | Info | Debug
}

export class MessageManager extends Morpho.System.Widget {
    protected getNumberOfMessages(): number {
        return this.getMessageEls().length;
    }

    protected getMessageEls(): JQuery {
        return this.el.find('.alert');
    }
}

export class PageMessageManager extends MessageManager {
    protected registerEventHandlers(): void {
        super.registerEventHandlers();
        this.registerCloseMessageHandler();
    }

    protected registerCloseMessageHandler(): void {
        var self = this;

        function hideElWithAnim($el: JQuery, fn: () => void) {
            $el.fadeOut(fn);
        }

        function hideMainContainerWithAnim() {
            hideElWithAnim(self.el, function () {
                self.el.find('.messages').remove();
                self.el.hide();
            });
        }

        function closeMessageWithAnim($message: JQuery): any {
            if (self.getNumberOfMessages() === 1) {
                hideMainContainerWithAnim();
            } else {
                var $messageContainer = $message.closest('.messages');
                if ($messageContainer.find('.alert').length === 1) {
                    hideElWithAnim($messageContainer, function () {
                        $messageContainer.remove();
                    });
                } else {
                    hideElWithAnim($message, function () {
                        $message.remove();
                    });
                }
            }
        }

        this.el.on('click', 'button.close', function () {
            closeMessageWithAnim($(this).closest('.alert'));
        });
        setTimeout(function () {
            hideMainContainerWithAnim();
        }, 5000);
    }
}

function messageTypeToString(messageType: MessageType): string {
    switch (messageType) {
        case MessageType.Debug:
            return 'debug';
        case MessageType.Info:
            return 'info';
        case MessageType.Warning:
            return 'warning';
        case MessageType.Error:
            return 'error';
        default:
            throw new Error("Invalid message type");
    }
}

export class Message {
    constructor(public type: MessageType, public text: string) {
    }

    public typeToString(): string {
        return messageTypeToString(this.type);
    }

    public hasType(type: MessageType): boolean {
        return this.type === type;
    }
}