define("system/test/message-test", ["require", "exports", "../lib/message"], function (require, exports, message_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    describe("Message", function () {
        [message_1.MessageType.Error, message_1.MessageType.Warning, message_1.MessageType.Info, message_1.MessageType.Debug].forEach(function (messageType) {
            it('renderMessage() - all message types', function () {
                var text = '<div>Random {0} warning "!" {1} has been occurred.</div>';
                var args = ['<b>system</b>', '<div>for <b>unknown</b> reason</div>'];
                var message = new message_1.Message(messageType, text, args);
                var cssClass = message_1.MessageType[messageType].toLowerCase();
                expect(message_1.renderMessage(message)).toEqual('<div class="' + cssClass + '">&lt;div&gt;Random <b>system</b> warning &quot;!&quot; <div>for <b>unknown</b> reason</div> has been occurred.&lt;/div&gt;</div>');
            });
        });
    });
});
