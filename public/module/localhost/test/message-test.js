define("localhost/test/message-test", ["require", "exports", "../lib/message", "localhost/lib/test/check"], function (require, exports, message_1, check_1) {
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
                const text = '<div>Random {0} warning "!" {1} has been occurred.</div>';
                const args = ['<b>system</b>', '<div>for <b>unknown</b> reason</div>'];
                const message = new message_1.Message(messageType, text, args);
                const cssClass = message_1.MessageType[messageType].toLowerCase();
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibWVzc2FnZS10ZXN0LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsibWVzc2FnZS10ZXN0LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7OztJQVdBLFFBQVEsQ0FBQyxTQUFTLEVBQUU7UUFXaEIsUUFBUSxDQUFDLGdCQUFnQixFQUFFO1lBQ3ZCLEVBQUUsQ0FBQyxLQUFLLEVBQUU7Z0JBQ04saUJBQVMsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUNwQixDQUFDLENBQUMsQ0FBQztRQUNQLENBQUMsQ0FBQyxDQUFDO1FBRUgsQ0FBQyxxQkFBVyxDQUFDLEtBQUssRUFBRSxxQkFBVyxDQUFDLE9BQU8sRUFBRSxxQkFBVyxDQUFDLElBQUksRUFBRSxxQkFBVyxDQUFDLEtBQUssQ0FBQyxDQUFDLE9BQU8sQ0FBQyxVQUFVLFdBQXdCO1lBQ3BILEVBQUUsQ0FBQyxxQ0FBcUMsRUFBRTtnQkFDdEMsTUFBTSxJQUFJLEdBQUcsMERBQTBELENBQUM7Z0JBQ3hFLE1BQU0sSUFBSSxHQUFHLENBQUMsZUFBZSxFQUFFLHNDQUFzQyxDQUFDLENBQUM7Z0JBQ3ZFLE1BQU0sT0FBTyxHQUFHLElBQUksaUJBQU8sQ0FBQyxXQUFXLEVBQUUsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO2dCQUVyRCxNQUFNLFFBQVEsR0FBRyxxQkFBVyxDQUFDLFdBQVcsQ0FBQyxDQUFDLFdBQVcsRUFBRSxDQUFDO2dCQUV4RCxrQkFBVSxDQUFDLGNBQWMsR0FBRyxRQUFRLEdBQUcsbUlBQW1JLEVBQUUsdUJBQWEsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO1lBQ3hNLENBQUMsQ0FBQyxDQUFDO1FBQ1AsQ0FBQyxDQUFDLENBQUM7UUFFSCxRQUFRLENBQUMsZUFBZSxFQUFFO1lBQ3RCLEVBQUUsQ0FBQyxLQUFLLEVBQUU7Z0JBQ04saUJBQVMsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUNwQixDQUFDLENBQUMsQ0FBQztRQUNQLENBQUMsQ0FBQyxDQUFDO0lBT1AsQ0FBQyxDQUFDLENBQUMifQ==