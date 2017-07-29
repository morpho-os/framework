import {renderMessage, Message, MessageType} from "../lib/message";

describe("Message", function() {
/*
    beforeEach(function () {
    });
*/

/*
    afterEach(function () {
        $('#message-manager-test').empty();
        $('#message-test').next('.info').remove().end().prev('.info').remove();
        $('#message-test').children(':not(.inner)').remove();
    });
*/

    [MessageType.Error, MessageType.Warning, MessageType.Info, MessageType.Debug].forEach(function (messageType: MessageType) {
        it('renderMessage() - all message types', function () {
            const text = '<div>Random {0} warning "!" {1} has been occurred.</div>';
            const args = ['<b>system</b>', '<div>for <b>unknown</b> reason</div>'];
            const message = new Message(messageType, text, args);

            const cssClass = MessageType[messageType].toLowerCase();

            expect(renderMessage(message)).toEqual('<div class="' + cssClass + '">&lt;div&gt;Random <b>system</b> warning &quot;!&quot; <div>for <b>unknown</b> reason</div> has been occurred.&lt;/div&gt;</div>');
        });
    });
/*
    const $el = $('#message-test');
    const prev = $el.prev.bind($el);
    const next = $el.next.bind($el);

    function checkNoEl(sel: (selector?: string) => JQuery): void {
        expect(sel('.info').length).toEqual(0);
    }
    function checkIsHelloEl(sel: (selector?: string) => JQuery): void {
        checkElHasHelloText(sel('.info'));
    }
    function checkElHasHelloText($el: JQuery): void {
        expect($el.text()).toEqual('Hello');
    }
    function checkIsInnerEl($el: JQuery): void {
        expect($el.hasClass('inner') && $el.text() === 'Inner').toBeTruthy();
    }

    ['Hello', new Message(MessageType.Info, 'Hello')].forEach((message) => {
        it('showMessage() default', function () {
            showMessage($el, message);
            checkNoEl(prev);
            checkIsHelloEl(next);
        });

        it('showMessage() after', function () {
            showMessage($el, message, Placement.After);
            checkNoEl(prev);
            checkIsHelloEl(next);
        });

        it('showMessage() before', function () {
            showMessage($el, message, Placement.Before);
            checkIsHelloEl(prev);
            checkNoEl(next);
        });

        it('showMessage() prepend', function () {
            showMessage($el, message, Placement.Prepend);
            checkNoEl(prev);
            checkNoEl(next);
            const $children = $el.children();
            expect($children.length).toEqual(2);
            checkElHasHelloText($children.eq(0));
            checkIsInnerEl($children.eq(1));
        });
    });

    describe('MessageManager', function () {
        const messageManager = new MessageManager($('#message-manager-test'));

        it('customRenderer', function () {
            messageManager.
        });
    });
    */
});
