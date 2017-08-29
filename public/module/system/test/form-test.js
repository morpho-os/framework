var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
define("system/test/form-test", ["require", "exports", "../lib/form", "../lib/widget", "../lib/message", "../lib/check"], function (require, exports, form_1, widget_1, message_1, check_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var Page = (function () {
        function Page() {
        }
        Page.requireEl = function () {
            return Page.el("#with-required-el-form [type=text]");
        };
        Page.nonRequiredEl = function () {
            return Page.el("#with-values-form [name=textBox]");
        };
        Page.el = function (selector) {
            var $el = $(selector);
            if (!$el.length) {
                throw new Error();
            }
            return $el;
        };
        Page.emptyFormEl = function () {
            return Page.el('#empty-form');
        };
        Page.emptyForm = function () {
            return new form_1.Form(Page.emptyFormEl());
        };
        Page.withRequiredElsFormEl = function () {
            return Page.el('#with-required-el-form');
        };
        Page.withRequiredElsForm = function () {
            return new form_1.Form(Page.withRequiredElsFormEl());
        };
        Page.numberOfElsOfNonEmptyForm = 26;
        return Page;
    }());
    describe("Form", function () {
        afterEach(function () {
            var $form = $('form');
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
                var $el = Page.requireEl();
                var errors = form_1.validateEl($el);
                check_1.checkLength(1, errors);
                check_1.checkEqual(errors[0], form_1.RequiredElValidator.EmptyValueMessage);
            });
            it('validateEl() non-required element', function () {
                var $el = Page.nonRequiredEl();
                var errors = form_1.validateEl($el);
                check_1.checkEmpty(errors);
            });
            it('elsToValidate() excludes submit buttons', function () {
                var $form = Page.el('#non-empty-form');
                var form = new form_1.Form($form);
                var $elsToValidate = form.elsToValidate();
                var i = 0;
                $elsToValidate.each(function () {
                    var $el = $(this);
                    check_1.checkFalse($el.is(':submit'));
                    i++;
                });
                check_1.checkEqual(Page.numberOfElsOfNonEmptyForm - 2, i);
            });
            it("validate() of the empty form", function () {
                var $form = Page.emptyFormEl();
                var form = new form_1.Form($form);
                check_1.checkTrue(form.validate());
                checkNoFormErrors($form);
            });
            it('validate() with required elements', function () {
                var $form = Page.withRequiredElsFormEl();
                var form = new form_1.Form($form);
                check_1.checkFalse(form.hasErrors());
                check_1.checkFalse(form.validate());
                check_1.checkNoEl($form.find('.messages'));
                check_1.checkNoEl($form.find('.alert-error'));
                var $invalidEls = form.invalidEls();
                check_1.checkEqual('input', $invalidEls.get(0).tagName.toLowerCase());
                check_1.checkEqual('text', $invalidEls.eq(0).attr('type'));
                check_1.checkEqual('textarea', $invalidEls.get(1).tagName.toLowerCase());
                var invalidCssClass = form.invalidCssClass;
                var i = 0;
                $invalidEls.each(function () {
                    var $el = $(this);
                    check_1.checkEqual(form_1.RequiredElValidator.EmptyValueMessage, $el.next().text());
                    check_1.checkTrue($el.hasClass(invalidCssClass));
                    var $elContainer = $el.closest('.' + form.elContainerCssClass);
                    check_1.checkTrue($elContainer.hasClass(invalidCssClass));
                    check_1.checkTrue($elContainer.hasClass('has-error'));
                    i++;
                });
                check_1.checkEqual(2, i);
                check_1.checkLength(1, $form.find('.has-error'));
                var $button = $form.find('input[type=button]');
                check_1.checkLength(1, $button);
                check_1.checkTrue(form.hasErrors());
                check_1.checkTrue($form.hasClass(invalidCssClass));
                form.removeErrors();
                check_1.checkFalse($form.hasClass(invalidCssClass));
                check_1.checkFalse(form.hasErrors());
                checkNoFormErrors($form);
            });
            it('Hides errors after node change', function () {
                var $form = Page.withRequiredElsFormEl();
                var form = new form_1.Form($form);
                form.validate();
                var $textarea = $form.find('textarea');
                var errorEl = function () { return $textarea.next('.error'); };
                check_1.checkLength(1, errorEl());
                $textarea.trigger('change');
                check_1.checkLength(0, errorEl());
            });
            it('invalidEls() before validation', function () {
                var form = new form_1.Form(Page.withRequiredElsFormEl());
                check_1.checkLength(0, form.invalidEls());
            });
            it('Form has "novalidate" attribute', function () {
                var $el = Page.emptyFormEl();
                expect($el.attr('novalidate')).toBeUndefined();
                new form_1.Form($el);
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
            var form = new form_1.Form($('#non-empty-form'));
            check_1.checkLength(Page.numberOfElsOfNonEmptyForm, form.els());
        });
        it('els() empty form', function () {
            check_1.checkLength(0, Page.emptyForm().els());
        });
        it('elValue()', function () {
            var $form = $('#with-values-form');
            check_1.checkEqual('bar', form_1.Form.elValue($form.find("[name='textBox']")));
            var $checkbox = $form.find("[name='checkBox']");
            check_1.checkEqual(0, form_1.Form.elValue($checkbox));
            $checkbox.prop('checked', true);
            check_1.checkEqual(1, form_1.Form.elValue($checkbox));
        });
        it('send() - response errors', function (done) {
            var form = new form_1.Form($('#server-error-form'));
            form.send()
                .then(function () {
                check_1.checkTrue(form.hasErrors());
                done();
            });
        });
        it('send() - success response', function (done) {
            var RedirectForm = (function (_super) {
                __extends(RedirectForm, _super);
                function RedirectForm() {
                    return _super !== null && _super.apply(this, arguments) || this;
                }
                RedirectForm.prototype.handleResponseSuccess = function (responseData) {
                    this.successHandlerArgs = Array.prototype.slice.call(arguments);
                };
                return RedirectForm;
            }(form_1.Form));
            var form = new RedirectForm($('#redirect-form'));
            form.send()
                .then(function () {
                check_1.checkEqual([{ redirect: "/go/to/linux" }], form.successHandlerArgs);
                done();
            });
        });
        it("Default browser's submit handler is not called", function (done) {
            var TestForm = (function (_super) {
                __extends(TestForm, _super);
                function TestForm() {
                    return _super !== null && _super.apply(this, arguments) || this;
                }
                TestForm.prototype.ajaxSuccess = function (responseData, textStatus, jqXHR) {
                    this.ajaxHandlerCalled = true;
                };
                return TestForm;
            }(form_1.Form));
            var $form = Page.withRequiredElsFormEl();
            var form = new TestForm($form);
            form.skipValidation = true;
            $form.trigger('submit');
            var intervalId = setInterval(function () {
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
            var $form = Page.emptyFormEl();
            var form = new form_1.Form($form);
            var messageText = "This is a test";
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
//# sourceMappingURL=form-test.js.map
