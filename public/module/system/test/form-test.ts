/// <reference path="../lib/index.d.ts"/>

import {Form, RequiredElValidator, validateEl} from "../lib/form";
import {Widget} from "../lib/widget";
import {ErrorMessage} from "../lib/message";
import {checkEmpty, checkEqual, checkFalse, checkLength, checkTrue, checkNoEl} from "../lib/check";

class Page {
    public static readonly numberOfElsOfNonEmptyForm = 26;

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

    public static emptyFormEl(): JQuery {
        return Page.el('#empty-form');
    }

    public static emptyForm(): Form {
        return new Form(Page.emptyFormEl());
    }

    public static withRequiredElsFormEl(): JQuery {
        return Page.el('#with-required-el-form');
    }

    public static withRequiredElsForm(): any {
        return new Form(Page.withRequiredElsFormEl());
    }
}

describe("Form", function () {
    afterEach(function () {
        const $form = $('form');
        $form.removeAttr('novalidate');
        $form.find('.' + Form.defaultInvalidCssClass).addBack().removeClass(Form.defaultInvalidCssClass);
        $form.find('.has-error').removeClass('has-error');
        $form.find('.error')
            .add($form.find('.alert'))
            .add($form.find('.messages'))
            .remove();
        $form.find('input[type=checkbox]').prop('checked', false);
    });

    describe('Validation', function () {
        function checkNoFormErrors($form: JQuery): void {
            checkNoEl($form.find('.has-error'));
            checkNoEl($form.find('.error'));
            checkNoEl($form.find('.messages'));
            checkNoEl($form.find('.alert-error'));
        }

        it('validateEl() required element', function () {
            const $el = Page.requireEl();
            const errors = validateEl($el);
            checkLength(1, errors);
            checkEqual(errors[0], RequiredElValidator.EmptyValueMessage);
        });

        it('validateEl() non-required element', function () {
            const $el = Page.nonRequiredEl();
            const errors = validateEl($el);
            checkEmpty(errors);
        });

        it('elsToValidate() excludes submit buttons', function () {
            const $form = Page.el('#non-empty-form');
            const form = new Form($form);
            const $elsToValidate = form.elsToValidate();
            let i = 0;
            $elsToValidate.each(function (this: Element) {
                const $el = $(this);
                checkFalse($el.is(':submit'));
                i++;
            });
            checkEqual(Page.numberOfElsOfNonEmptyForm - 2, i);
        });
        it("validate() of the empty form", function () {
            const $form = Page.emptyFormEl();
            const form = new Form($form);
            checkTrue(form.validate());
            checkNoFormErrors($form);
        });

        it('validate() with required elements', function () {
            const $form = Page.withRequiredElsFormEl();
            const form = new Form($form);

            checkFalse(form.hasErrors());

            checkFalse(form.validate());

            checkNoEl($form.find('.messages'));
            checkNoEl($form.find('.alert-error'));

            const $invalidEls = form.invalidEls();

            checkEqual('input', $invalidEls.get(0).tagName.toLowerCase());
            checkEqual('text', $invalidEls.eq(0).attr('type'));
            //checkTrue(form.elHasErrors($invalidEls.eq(0)));

            checkEqual('textarea', $invalidEls.get(1).tagName.toLowerCase());
            //checkTrue(form.elHasErrors($invalidEls.eq(1)));

            const invalidCssClass = form.invalidCssClass;

            let i = 0;
            $invalidEls.each(function (this: Element) {
                const $el = $(this);
                checkEqual(RequiredElValidator.EmptyValueMessage, $el.next().text());
                checkTrue($el.hasClass(invalidCssClass));

                const $elContainer = $el.closest('.' + form.elContainerCssClass);
                checkTrue($elContainer.hasClass(invalidCssClass));
                checkTrue($elContainer.hasClass('has-error'));

                i++;
            });
            checkEqual(2, i);
            checkLength(1, $form.find('.has-error'));

            const $button = $form.find('input[type=button]');
            checkLength(1, $button);
            //checkFalse(form.elHasErrors($button));

            checkTrue(form.hasErrors());
            checkTrue($form.hasClass(invalidCssClass));

            form.removeErrors();

            checkFalse($form.hasClass(invalidCssClass));
            checkFalse(form.hasErrors());

            checkNoFormErrors($form);
        });

        it('Hides errors after node change', function () {
            const $form = Page.withRequiredElsFormEl();
            const form = new Form($form);

            form.validate();

            const $textarea = $form.find('textarea');

            const errorEl = () => $textarea.next('.error');

            checkLength(1, errorEl());

            $textarea.trigger('change');

            checkLength(0, errorEl());
        });

        it('invalidEls() before validation', function () {
            const form = new Form(Page.withRequiredElsFormEl());
            checkLength(0, form.invalidEls());
        });

        it('Form has "novalidate" attribute', function () {
            const $el = Page.emptyFormEl();
            expect($el.attr('novalidate')).toBeUndefined();
            // tslint:disable-next-line:no-unused-new
            new Form($el);
            checkEqual('novalidate', $el.attr('novalidate'));
        });
    });

    it('Is Widget', function () {
        checkTrue(Page.emptyForm() instanceof Widget);
    });

    it('isRequiredEl()', function () {
        checkTrue(Form.isRequiredEl(Page.requireEl()));
        checkFalse(Form.isRequiredEl(Page.nonRequiredEl()));
    });

    it('els() non-empty form', function () {
        const form = new Form($('#non-empty-form'));
        // all elements except type="image"
        checkLength(Page.numberOfElsOfNonEmptyForm, form.els());
    });

    it('els() empty form', function () {
        checkLength(0, Page.emptyForm().els());
    });

    it('elValue()', function () {
        const $form = $('#with-values-form');

        checkEqual('bar', Form.elValue($form.find("[name='textBox']")));

        const $checkbox = $form.find("[name='checkBox']");

        checkEqual(0, Form.elValue($checkbox));

        $checkbox.prop('checked', true);
        checkEqual(1, Form.elValue($checkbox));
    });

    it('send() - response errors', function (done) {
        const form = new Form($('#server-error-form'));
        form.send()
            .then(() => {
                checkTrue(form.hasErrors());
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
                checkEqual([{redirect: "/go/to/linux"}], form.successHandlerArgs);
                done();
            });
    });

    it("Default browser's submit handler is not called", function (done: DoneFn) {
        class TestForm extends Form {
            public ajaxHandlerCalled: boolean;

            protected ajaxSuccess(responseData: any, textStatus: string, jqXHR: JQueryXHR): any {
                this.ajaxHandlerCalled = true;
            }

/*            protected ajaxError(jqXHR: JQueryXHR, textStatus: string, errorThrown: string): any {
                this.ajaxHandlerCalled = true;
            }*/
        }

        const $form = Page.withRequiredElsFormEl();
        const form = new TestForm($form);
        form.skipValidation = true;
        $form.trigger('submit');

        const intervalId = setInterval(function () {
            if (form.ajaxHandlerCalled) {
                clearInterval(intervalId);
                checkTrue(true);
                done();
            }
        }, 200);
    });

    it('hasErrors() initial state', function () {
        checkFalse(Page.withRequiredElsForm().hasErrors());
    });

    it('hasErrors() after showErrors()', function () {
        const $form = Page.emptyFormEl();
        const form = new Form($form);

        const messageText = "This is a test";
        form.showErrors([new ErrorMessage(messageText)]);

        function formMessageContainerEl() {
            return $form.find('.' + form.formMessageContainerCssClass);
        }

        checkTrue(form.hasErrors());
        checkEqual(messageText, formMessageContainerEl().text());

        form.removeErrors();

        checkNoEl(formMessageContainerEl());
        checkFalse(form.hasErrors()) ;
    });
});
