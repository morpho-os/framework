/// <reference path="../lib/index.d.ts"/>

import {Form, RequiredElValidator, validateEl} from "../lib/form";
// import {Message, MessageType} from "../lib/message";
import {Widget} from "../lib/widget";

class Page {
    public static requireEl(): JQuery {
        return Page.el("#with-required-el-form [type=text]");
    }

    public static nonRequiredEl(): JQuery {
        return Page.el("#with-values-form [name=textBox]");
    }

    public static el(selector: string): JQuery {
        const $el = $(selector);
        if (!$el.length) {
            throw new Error();
        }
        return $el;
    }

    public static emptyForm(): Form {
        return new Form(Page.emptyFormEl());
    }

    public static withRequiredElsFormEl(): JQuery {
        return Page.el('#with-required-el-form');
    }

    public static emptyFormEl(): JQuery {
        return Page.el('#empty-form');
    }
}

describe("Form", function () {
    const numberOfPossibleFormEls = 26;

    afterEach(function () {
        const $form = $('form');
        $form.each(function (this: HTMLFormElement) {
            $(this).removeAttr('novalidate');
            $(this.elements).each(function (this: Element) {
                $(this).removeClass(Form.invalidCssClass);
                $(this).closest('.form-group').removeClass(Form.invalidCssClass);
            });
        });
        $form.find('.error').remove();
        $form.find('.alert').remove();
        $form.find('input[type=checkbox]').prop('checked', false);
        $form.find('.' + Form.invalidCssClass).addBack().removeClass(Form.invalidCssClass);
    });

    describe('Validation', function () {
        it('validateEl() required element', function () {
            const $el = Page.requireEl();
            const errors = validateEl($el);
            expect(errors.length).toEqual(1);
            expect(errors[0]).toEqual(RequiredElValidator.EmptyValueMessage);
        });

        it('validateEl() non-required element', function () {
            const $el = Page.nonRequiredEl();
            const errors = validateEl($el);
            expect(errors.length).toEqual(0);
        });

        it('elsToValidate() excludes submit buttons', function () {
            const $form = Page.el('#non-empty-form');
            const form = new Form($form);
            const $elsToValidate = form.elsToValidate();
            let i = 0;
            $elsToValidate.each(function (this: Element) {
                const $el = $(this);
                expect($el.is(':submit')).toBeFalsy();
                i++;
            });
            expect(i).toEqual(numberOfPossibleFormEls - 2);
        });

        it("validate() of the empty form", function () {
            const form = Page.emptyForm();
            expect(form.validate()).toBeTruthy();
        });

        it('validate() with required elements', function () {
            const $form = Page.withRequiredElsFormEl();
            const form = new Form($form);

            expect(form.hasValidationErrors()).toBeFalsy();

            expect(form.validate()).toBeFalsy();

            const $invalidEls = form.invalidEls();

            expect($invalidEls.get(0).tagName.toLowerCase()).toEqual('input');
            expect($invalidEls.eq(0).attr('type')).toEqual('text');

            expect($invalidEls.get(1).tagName.toLowerCase()).toEqual('textarea');

            const invalidCssClass = Form.invalidCssClass;

            let i = 0;
            $invalidEls.each(function (this: Element) {
                const $el = $(this);
                expect($el.next().text()).toEqual(RequiredElValidator.EmptyValueMessage);
                expect($el.hasClass(invalidCssClass)).toBeTruthy();
                // console.log($el.closest('.form-group'));
                expect($el.closest('.form-group').hasClass(invalidCssClass)).toBeTruthy();
                i++;
            });
            expect(i).toEqual(2);

            expect(form.hasValidationErrors()).toBeTruthy();
            expect($form.hasClass(invalidCssClass)).toBeTruthy();

            form.clearValidationErrors();

            expect($form.hasClass(invalidCssClass)).toBeFalsy();
            expect(form.hasValidationErrors()).toBeFalsy();

            expect($form.find('.error').length).toEqual(0);
        });

        it('invalidEls() before validation', function () {
            const form = new Form(Page.withRequiredElsFormEl());
            expect(form.invalidEls().length).toEqual(0);
        });

        it('Has "novalidate" attribute', function () {
            const $el = Page.emptyFormEl();
            expect($el.attr('novalidate')).toBeUndefined();
            // tslint:disable-next-line:no-unused-new
            new Form($el);
            expect($el.attr('novalidate')).toEqual('novalidate');
        });
    });

    it('Is Widget', function () {
        expect(new Form($()) instanceof Widget).toBeTruthy();
    });

    it('isRequiredEl()', function () {
        expect(
            Form.isRequiredEl(Page.requireEl())
        ).toBeTruthy();

        expect(
            Form.isRequiredEl(Page.nonRequiredEl())
        ).toBeFalsy();
    });

    it('els() non-empty form', function () {
        const form = new Form($('#non-empty-form'));
        // all elements except type="image"
        expect(form.els().length).toEqual(numberOfPossibleFormEls);
    });

    it('els() empty form', function () {
        expect(Page.emptyForm().els().length).toEqual(0);
    });

    it('elValue()', function () {
        const $form = $('#with-values-form');

        expect(Form.elValue($form.find("[name='textBox']"))).toEqual('bar');

        const $checkbox = $form.find("[name='checkBox']");
        expect(Form.elValue($checkbox)).toEqual(0);
        $checkbox.prop('checked', true);
        expect(Form.elValue($checkbox)).toEqual(1);
    });

    it('send() - response errors', function (done) {
        const form = new Form($('#server-error-form'));
        expect(form.hasValidationErrors()).toBeFalsy();
        form.send()
            .then(() => {
                expect(form.hasValidationErrors()).toBeTruthy();
                done();
            });
    });

    it('send() - success response', function (done) {
        class RedirectForm extends Form {
            public successHandlerArgs: any;

            protected handleResponseSuccess(responseData: any): any {
                this.successHandlerArgs = Array.prototype.slice.call(arguments);
            }
        }
        const form = new RedirectForm($('#redirect-form'));
        form.send()
            .then(() => {
                expect(form.successHandlerArgs).toEqual([{redirect: "/go/to/linux"}]);
                done();
            });
    });

    it('Default submit handler is not called', function (done: DoneFn) {
        class TestForm extends Form {
            public handleResponseCalled: boolean;

            protected handleResponse(responseData: JsonResponse): void {
                this.handleResponseCalled = true;
            }
        }

        const $form = Page.withRequiredElsFormEl();
        const form = new TestForm($form);
        form.skipValidation = true;
        $form.trigger('submit');

        const intervalId = setInterval(function () {
            if (form.handleResponseCalled) {
                clearInterval(intervalId);
                expect(true).toBeTruthy(true);
                done();
            }
        }, 200);
    });
/*

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

            it('Handling of submit errors', function () {
                const $form = $('form:eq(3)');
                const form = new Form($form);
                $form.trigger('submit');
                form.
            });
        */
});
