import {Form} from "../lib/form"
import {Message, MessageType} from "../lib/message";

describe("Form", function() {
/*    let form: Form;

    beforeEach(function() {
        form = new Form($('form:eq(0)'));
    });*/

    it("validate() of the empty form", function() {
        const form = new Form($('form:eq(0)'));
        expect(form.wasValidated()).toBeFalsy();
        expect(form.validate()).toBeTruthy();
        expect(form.wasValidated()).toBeTruthy();
        expect(form.isValid()).toBeTruthy();
    });

    it('validate() with require elements', function () {
        const form = new Form($('form:eq(2)'));

        expect(form.validate()).toBeFalsy();

        const $invalidEls = form.invalidEls();

        expect($invalidEls.length).toEqual(2);

        expect($invalidEls.get(0).tagName.toLowerCase()).toEqual('input');
        expect($invalidEls.eq(0).attr('type')).toEqual('text');

        expect($invalidEls.get(1).tagName.toLowerCase()).toEqual('textarea');
    });

    it('invalidEls() before validation', function () {
        const form = new Form($('form:eq(2)'));
        expect(form.invalidEls().length).toEqual(0);
    });

    it('els() empty form', function () {
        const form = new Form($('form:eq(0)'));
        expect(form.els().length).toEqual(0);
    });

    it('els() non-empty form', function () {
        const form = new Form($('form:eq(1)'));
        // all elements except type="image"
        expect(form.els().length).toEqual(26);
    });

    it('hasErrors() throws exception if was not validated', function () {
        const form = new Form($('form:eq(2)'));
        expect(() => form.hasErrors()).toThrowError("Unable to check state, the form should be validated first");
    });

    it('formMessages()', function () {
        const form = new Form($('form:eq(2)'));
        expect(form.formMessages(MessageType.Error).length).toEqual(0);

        const message = new Message(MessageType.Error, 'test');
        form.addFormMessage(<Message>message);

        expect(form.formMessages(MessageType.Error)).toEqual([message]);
        expect(form.formMessages(MessageType.Info)).toEqual([]);
    });

    it('formErrorMessages', function () {
        const form = new Form($('form:eq(2)'));
        expect(form.formErrorMessages()).toEqual([]);

        form.addFormErrorMessage('Something wrong');

        expect(form.formErrorMessages().length).toEqual(1);
    });
});