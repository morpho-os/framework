define("system/test/message-test", ["require", "exports", "../lib/message", "../lib/check"], function (require, exports, message_1, check_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    describe("Message", function () {
        describe('this is before', function () {
            it('foo', function () {
                check_1.checkTrue(true);
            });
        });
        [message_1.MessageType.Error, message_1.MessageType.Warning, message_1.MessageType.Info, message_1.MessageType.Debug].forEach(function (messageType) {
            it('renderMessage() - all message types', function () {
                var text = '<div>Random {0} warning "!" {1} has been occurred.</div>';
                var args = ['<b>system</b>', '<div>for <b>unknown</b> reason</div>'];
                var message = new message_1.Message(messageType, text, args);
                var cssClass = message_1.MessageType[messageType].toLowerCase();
                check_1.checkEqual('<div class="' + cssClass + '">&lt;div&gt;Random <b>system</b> warning &quot;!&quot; <div>for <b>unknown</b> reason</div> has been occurred.&lt;/div&gt;</div>', message_1.renderMessage(message));
            });
        });
        describe('this is after', function () {
            it('foo', function () {
                check_1.checkTrue(true);
            });
        });
    });
});
//# sourceMappingURL=message-test.js.map
