import {Widget} from "./widget";

export const enum MessageType {
    Error = 1,
    Warning = 2,
    Info = 4,
    Debug = 8,
    All = Error | Warning | Info | Debug
}

interface MyWindow extends Window {
    pageMessenger: PageMessenger;
}
declare const window: MyWindow;

export function initPageMessenger(): PageMessenger {
    window.pageMessenger = new PageMessenger('#page-messages');
    return window.pageMessenger;
}

export class PageMessenger extends Widget {
    protected numberOfMessages(): number {
        return this.messageEls().length;
    }

    protected messageEls(): JQuery {
        return this.el.find('.alert');
    }

    protected registerEventHandlers(): void {
        super.registerEventHandlers();
        this.registerCloseMessageHandler();
    }

    protected registerCloseMessageHandler(): void {
        const self = this;

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
            if (self.numberOfMessages() === 1) {
                hideMainContainerWithAnim();
            } else {
                const $messageContainer = $message.closest('.messages');
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

        this.el.on('click', 'button.close', function (this: JQuery) {
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