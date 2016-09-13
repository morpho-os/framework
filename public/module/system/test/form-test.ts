/// <reference path="../src/d.ts/form.d.ts"/>
/// <reference path="../src/d.ts/test-case.d.ts"/>
/// <reference path="../src/d.ts/message.d.ts"/>

import Form = System.Form;
import TestCase = System.TestCase;
import MessageType = System.MessageType;
import Message = System.Message;

export class FormTest extends TestCase {
    protected testValidate_EmptyForm(): void {
        var form = new Form($('form:eq(0)'));
        this.assertFalse(form.wasValidated());
        this.assertTrue(form.validate());
        this.assertTrue(form.wasValidated());
        this.assertTrue(form.isValid());
    }

    protected testValidate_RequiredElements(): void {
        var form = new Form($('form:eq(2)'));
        this.assertFalse(form.validate());
        var $invalidEls = form.getInvalidEls();

    }

    protected testGetInvalidEls_BeforeValidation(): void {
        var form = new Form($('form:eq(2)'));
        this.assertEquals([], form.getInvalidEls());
    }
/*
    protected testGetEls(): void {
        var form = new Form($('form:eq(0)'));
        this.assertEquals(0, form.getEls().length);

        var form = new Form($('form:eq(1)'));
        // all elements except type="image"
        this.assertEquals(26, form.getEls().length);
    }




    protected testHasErrors_ThrowsExceptionIfWasNotValidated(): void {
        var form = new Form($('form:eq(2)'));
        try {
            form.hasErrors();
            this.fail();
        } catch (e) {
            this.assertEquals("Unable to check state, the form should be validated first", e.message);
        }
    }

    protected testGenericMessageInterface(): void {
        var form = new Form($('form:eq(2)'));
        this.assertEquals([], form.getMessages(MessageType.Error));

        var message = new Message(MessageType.Error, 'test');
        form.addMessage(<Message>message);

        this.assertEquals([message], form.getMessages(MessageType.Error));
        this.assertEquals([], form.getMessages(MessageType.Info));
    }

    protected testSpecificMessageInterface(): void {
        var form = new Form($('form:eq(2)'));
        this.assertEquals([], form.getErrorMessages());

        form.addErrorMessage("Test");

        this.fail();
    }
*/
}
