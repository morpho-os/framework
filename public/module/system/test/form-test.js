define("system/test/form-test", ["require", "exports", "../lib/form", "../lib/message"], function (require, exports, form_1, message_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    describe("Form", function () {
        it("validate() of the empty form", function () {
            var form = new form_1.Form($('form:eq(0)'));
            expect(form.wasValidated()).toBeFalsy();
            expect(form.validate()).toBeTruthy();
            expect(form.wasValidated()).toBeTruthy();
            expect(form.isValid()).toBeTruthy();
        });
        it('validate() with require elements', function () {
            var form = new form_1.Form($('form:eq(2)'));
            expect(form.validate()).toBeFalsy();
            var $invalidEls = form.invalidEls();
            expect($invalidEls.length).toEqual(2);
            expect($invalidEls.get(0).tagName.toLowerCase()).toEqual('input');
            expect($invalidEls.eq(0).attr('type')).toEqual('text');
            expect($invalidEls.get(1).tagName.toLowerCase()).toEqual('textarea');
        });
        it('invalidEls() before validation', function () {
            var form = new form_1.Form($('form:eq(2)'));
            expect(form.invalidEls().length).toEqual(0);
        });
        it('els() empty form', function () {
            var form = new form_1.Form($('form:eq(0)'));
            expect(form.els().length).toEqual(0);
        });
        it('els() non-empty form', function () {
            var form = new form_1.Form($('form:eq(1)'));
            expect(form.els().length).toEqual(26);
        });
        it('hasErrors() throws exception if was not validated', function () {
            var form = new form_1.Form($('form:eq(2)'));
            expect(function () { return form.hasErrors(); }).toThrowError("Unable to check state, the form should be validated first");
        });
        it('formMessages()', function () {
            var form = new form_1.Form($('form:eq(2)'));
            expect(form.formMessages(1).length).toEqual(0);
            var message = new message_1.Message(1, 'test');
            form.addFormMessage(message);
            expect(form.formMessages(1)).toEqual([message]);
            expect(form.formMessages(4)).toEqual([]);
        });
        it('formErrorMessages', function () {
            var form = new form_1.Form($('form:eq(2)'));
            expect(form.formErrorMessages()).toEqual([]);
            form.addFormErrorMessage('Something wrong');
            expect(form.formErrorMessages().length).toEqual(1);
        });
    });
});
