define("localhost/test/form-test", ["require", "exports", "localhost/lib/form", "localhost/lib/widget", "localhost/lib/message", "localhost/lib/test/check"], function (require, exports, form_1, widget_1, message_1, check_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    class Page {
        static requireEl() {
            return Page.el("#with-required-el-form [type=text]");
        }
        static nonRequiredEl() {
            return Page.el("#with-values-form [name=textBox]");
        }
        static el(selector) {
            const $el = $(selector);
            if (!$el.length) {
                throw new Error();
            }
            return $el;
        }
        static emptyFormEl() {
            return Page.el('#empty-form');
        }
        static emptyForm() {
            return new form_1.Form({ el: Page.emptyFormEl() });
        }
        static withRequiredElsFormEl() {
            return Page.el('#with-required-el-form');
        }
        static withRequiredElsForm() {
            return new form_1.Form({ el: Page.withRequiredElsFormEl() });
        }
    }
    Page.numberOfElsOfNonEmptyForm = 26;
    describe("Form", function () {
        afterEach(function () {
            const $form = $('form');
            $form.removeAttr('novalidate');
            $form.find('.' + form_1.Form.defaultInvalidCssClass).addBack().removeClass(form_1.Form.defaultInvalidCssClass);
            $form.find('.has-error').removeClass('has-error');
            $form.find('.error')
                .add($form.find('.alert'))
                .add($form.find('.messages'))
                .remove();
            $form.find('input[type=checkbox]').prop('checked', false);
        });
        describe('Validation', function () {
            function checkNoFormErrors($form) {
                check_1.checkNoEl($form.find('.has-error'));
                check_1.checkNoEl($form.find('.error'));
                check_1.checkNoEl($form.find('.messages'));
                check_1.checkNoEl($form.find('.alert-error'));
            }
            it('validateEl() required element', function () {
                const $el = Page.requireEl();
                const errors = form_1.validateEl($el);
                check_1.checkLength(1, errors);
                check_1.checkEqual(errors[0], form_1.RequiredElValidator.EmptyValueMessage);
            });
            it('validateEl() non-required element', function () {
                const $el = Page.nonRequiredEl();
                const errors = form_1.validateEl($el);
                check_1.checkEmpty(errors);
            });
            it('elsToValidate() excludes submit buttons', function () {
                const $form = Page.el('#non-empty-form');
                const form = new form_1.Form({ el: $form });
                const $elsToValidate = form.elsToValidate();
                let i = 0;
                $elsToValidate.each(function () {
                    const $el = $(this);
                    check_1.checkFalse($el.is(':submit'));
                    i++;
                });
                check_1.checkEqual(Page.numberOfElsOfNonEmptyForm - 2, i);
            });
            it("validate() of the empty form", function () {
                const $form = Page.emptyFormEl();
                const form = new form_1.Form({ el: $form });
                check_1.checkTrue(form.validate());
                checkNoFormErrors($form);
            });
            it('validate() with required elements', function () {
                const $form = Page.withRequiredElsFormEl();
                const form = new form_1.Form({ el: $form });
                check_1.checkFalse(form.hasErrors());
                check_1.checkFalse(form.validate());
                check_1.checkNoEl($form.find('.messages'));
                check_1.checkNoEl($form.find('.alert-error'));
                const $invalidEls = form.invalidEls();
                check_1.checkEqual('input', $invalidEls.get(0).tagName.toLowerCase());
                check_1.checkEqual('text', $invalidEls.eq(0).attr('type'));
                check_1.checkEqual('textarea', $invalidEls.get(1).tagName.toLowerCase());
                const invalidCssClass = form.invalidCssClass;
                let i = 0;
                $invalidEls.each(function () {
                    const $el = $(this);
                    check_1.checkEqual(form_1.RequiredElValidator.EmptyValueMessage, $el.next().text());
                    check_1.checkTrue($el.hasClass(invalidCssClass));
                    const $elContainer = $el.closest('.' + form.elContainerCssClass);
                    check_1.checkTrue($elContainer.hasClass(invalidCssClass));
                    check_1.checkTrue($elContainer.hasClass('has-error'));
                    i++;
                });
                check_1.checkEqual(2, i);
                check_1.checkLength(1, $form.find('.has-error'));
                const $button = $form.find('input[type=button]');
                check_1.checkLength(1, $button);
                check_1.checkTrue(form.hasErrors());
                check_1.checkTrue($form.hasClass(invalidCssClass));
                form.removeErrors();
                check_1.checkFalse($form.hasClass(invalidCssClass));
                check_1.checkFalse(form.hasErrors());
                checkNoFormErrors($form);
            });
            it('Hides errors after node change', function () {
                const $form = Page.withRequiredElsFormEl();
                const form = new form_1.Form({ el: $form });
                form.validate();
                const $textarea = $form.find('textarea');
                const errorEl = () => $textarea.next('.error');
                check_1.checkLength(1, errorEl());
                $textarea.trigger('change');
                check_1.checkLength(0, errorEl());
            });
            it('invalidEls() before validation', function () {
                const form = new form_1.Form({ el: Page.withRequiredElsFormEl() });
                check_1.checkLength(0, form.invalidEls());
            });
            it('Form has "novalidate" attribute', function () {
                const $el = Page.emptyFormEl();
                expect($el.attr('novalidate')).toBeUndefined();
                new form_1.Form({ el: $el });
                check_1.checkEqual('novalidate', $el.attr('novalidate'));
            });
        });
        it('Is Widget', function () {
            check_1.checkTrue(Page.emptyForm() instanceof widget_1.Widget);
        });
        it('isRequiredEl()', function () {
            check_1.checkTrue(form_1.Form.isRequiredEl(Page.requireEl()));
            check_1.checkFalse(form_1.Form.isRequiredEl(Page.nonRequiredEl()));
        });
        it('els() non-empty form', function () {
            const form = new form_1.Form({ el: $('#non-empty-form') });
            check_1.checkLength(Page.numberOfElsOfNonEmptyForm, form.els());
        });
        it('els() empty form', function () {
            check_1.checkLength(0, Page.emptyForm().els());
        });
        it('elValue()', function () {
            const $form = $('#with-values-form');
            check_1.checkEqual('bar', form_1.Form.elValue($form.find("[name='textBox']")));
            const $checkbox = $form.find("[name='checkBox']");
            check_1.checkEqual(0, form_1.Form.elValue($checkbox));
            $checkbox.prop('checked', true);
            check_1.checkEqual(1, form_1.Form.elValue($checkbox));
        });
        it('send() - response errors', function (done) {
            const form = new form_1.Form({ el: $('#server-error-form') });
            form.send()
                .then(() => {
                check_1.checkTrue(form.hasErrors());
                done();
            });
        });
        it('send() - success response', function (done) {
            class RedirectForm extends form_1.Form {
                handleOkResponse(responseData) {
                    this.successHandlerArgs = Array.prototype.slice.call(arguments);
                }
            }
            const form = new RedirectForm({ el: $('#redirect-form') });
            form.send()
                .then(() => {
                check_1.checkEqual([{ redirect: "/go/to/linux" }], form.successHandlerArgs);
                done();
            });
        });
        it("Default browser's submit handler is not called", function (done) {
            class TestForm extends form_1.Form {
                ajaxSuccess(responseData, textStatus, jqXHR) {
                    this.ajaxHandlerCalled = true;
                }
                ajaxError(jqXHR, textStatus, errorThrown) {
                    this.ajaxHandlerCalled = true;
                }
            }
            const $form = Page.withRequiredElsFormEl();
            const form = new TestForm({ el: $form });
            form.skipValidation = true;
            $form.trigger('submit');
            const intervalId = setInterval(function () {
                if (form.ajaxHandlerCalled) {
                    clearInterval(intervalId);
                    check_1.checkTrue(true);
                    done();
                }
            }, 200);
        });
        it('hasErrors() initial state', function () {
            check_1.checkFalse(Page.withRequiredElsForm().hasErrors());
        });
        it('hasErrors() after showErrors()', function () {
            const $form = Page.emptyFormEl();
            const form = new form_1.Form({ el: $form });
            const messageText = "This is a test";
            form.showErrors([new message_1.ErrorMessage(messageText)]);
            function formMessageContainerEl() {
                return $form.find('.' + form.formMessageContainerCssClass);
            }
            check_1.checkTrue(form.hasErrors());
            check_1.checkEqual(messageText, formMessageContainerEl().text());
            form.removeErrors();
            check_1.checkNoEl(formMessageContainerEl());
            check_1.checkFalse(form.hasErrors());
        });
    });
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZm9ybS10ZXN0LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiZm9ybS10ZXN0LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7OztJQWFBLE1BQU0sSUFBSTtRQUdDLE1BQU0sQ0FBQyxTQUFTO1lBQ25CLE9BQU8sSUFBSSxDQUFDLEVBQUUsQ0FBQyxvQ0FBb0MsQ0FBQyxDQUFDO1FBQ3pELENBQUM7UUFFTSxNQUFNLENBQUMsYUFBYTtZQUN2QixPQUFPLElBQUksQ0FBQyxFQUFFLENBQUMsa0NBQWtDLENBQUMsQ0FBQztRQUN2RCxDQUFDO1FBRU0sTUFBTSxDQUFDLEVBQUUsQ0FBQyxRQUFnQjtZQUM3QixNQUFNLEdBQUcsR0FBRyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUM7WUFDeEIsSUFBSSxDQUFDLEdBQUcsQ0FBQyxNQUFNLEVBQUU7Z0JBQ2IsTUFBTSxJQUFJLEtBQUssRUFBRSxDQUFDO2FBQ3JCO1lBQ0QsT0FBTyxHQUFHLENBQUM7UUFDZixDQUFDO1FBRU0sTUFBTSxDQUFDLFdBQVc7WUFDckIsT0FBTyxJQUFJLENBQUMsRUFBRSxDQUFDLGFBQWEsQ0FBQyxDQUFDO1FBQ2xDLENBQUM7UUFFTSxNQUFNLENBQUMsU0FBUztZQUNuQixPQUFPLElBQUksV0FBSSxDQUFDLEVBQUMsRUFBRSxFQUFFLElBQUksQ0FBQyxXQUFXLEVBQUUsRUFBQyxDQUFDLENBQUM7UUFDOUMsQ0FBQztRQUVNLE1BQU0sQ0FBQyxxQkFBcUI7WUFDL0IsT0FBTyxJQUFJLENBQUMsRUFBRSxDQUFDLHdCQUF3QixDQUFDLENBQUM7UUFDN0MsQ0FBQztRQUVNLE1BQU0sQ0FBQyxtQkFBbUI7WUFDN0IsT0FBTyxJQUFJLFdBQUksQ0FBQyxFQUFDLEVBQUUsRUFBRSxJQUFJLENBQUMscUJBQXFCLEVBQUUsRUFBQyxDQUFDLENBQUM7UUFDeEQsQ0FBQzs7SUFoQ3NCLDhCQUF5QixHQUFHLEVBQUUsQ0FBQztJQW1DMUQsUUFBUSxDQUFDLE1BQU0sRUFBRTtRQUNiLFNBQVMs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