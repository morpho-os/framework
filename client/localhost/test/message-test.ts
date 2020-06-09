/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

/// <amd-module name="localhost/test/message-test" />

import {renderMessage, Message, MessageType} from "../lib/message";
import {checkEqual, checkTrue} from "../lib/test/check";

describe("Message", function() {
/*
    beforeEach(function () {
    });

    afterEach(function () {
        $('#message-manager-test').empty();
        $('#message-test').next('.info').remove().end().prev('.info').remove();
        $('#message-test').children(':not(.inner)').remove();
    });
*/
    describe('this is before', function () {
        it('foo', function () {
            checkTrue(true);
        });
    });

    [MessageType.Error, MessageType.Warning, MessageType.Info, MessageType.Debug].forEach(function (messageType: MessageType) {
        it('renderMessage() - all message types', function () {
            const text = '<div>Random {0} warning "!" {1} has been occurred.</div>';
            const args = ['<b>system</b>', '<div>for <b>unknown</b> reason</div>'];
            const message = new Message(messageType, text, args);

            const cssClass = MessageType[messageType].toLowerCase();

            checkEqual('<div class="' + cssClass + '">&lt;div&gt;Random <b>system</b> warning &quot;!&quot; <div>for <b>unknown</b> reason</div> has been occurred.&lt;/div&gt;</div>', renderMessage(message));
        });
    });

    describe('this is after', function () {
        it('foo', function () {
            checkTrue(true);
        });
    });
/*

        it('customRenderer', function () {
            messageManager.
        });
*/
});
