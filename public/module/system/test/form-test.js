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
        return Page;
    }());
    describe("Form", function () {
        var numberOfPossibleFormEls = 26;
        afterEach(function () {
            var $form = $('form');
            $form.each(function () {
                $(this).removeAttr('novalidate');
                $(this.elements).each(function () {
                    $(this).removeClass(form_1.Form.defaultInvalidCssClass);
                    $(this).closest('.form-group').removeClass(form_1.Form.defaultInvalidCssClass);
                });
            });
            $form.find('.error').remove();
            $form.find('.alert').remove();
            $form.find('input[type=checkbox]').prop('checked', false);
            $form.find('.' + form_1.Form.defaultInvalidCssClass).addBack().removeClass(form_1.Form.defaultInvalidCssClass);
        });
        describe('Validation', function () {
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
                check_1.checkEqual(numberOfPossibleFormEls - 2, i);
            });
            it("validate() of the empty form", function () {
                check_1.checkTrue(Page.emptyForm().validate());
            });
            it('validate() with required elements', function () {
                var $form = Page.withRequiredElsFormEl();
                var form = new form_1.Form($form);
                check_1.checkFalse(form.hasErrors());
                check_1.checkFalse(form.validate());
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
                    check_1.checkTrue($el.closest('.form-group').hasClass(invalidCssClass));
                    i++;
                });
                check_1.checkEqual(2, i);
                check_1.checkTrue(form.hasErrors());
                check_1.checkTrue($form.hasClass(invalidCssClass));
                form.clearErrors();
                check_1.checkFalse($form.hasClass(invalidCssClass));
                check_1.checkFalse(form.hasErrors());
                check_1.checkNoEl($form.find('.error'));
            });
            it('invalidEls() before validation', function () {
                var form = new form_1.Form(Page.withRequiredElsFormEl());
                check_1.checkEqual(0, form.invalidEls().length);
            });
            it('Has "novalidate" attribute', function () {
                var $el = Page.emptyFormEl();
                expect($el.attr('novalidate')).toBeUndefined();
                new form_1.Form($el);
                check_1.checkEqual('novalidate', $el.attr('novalidate'));
            });
        });
        it('Is Widget', function () {
            check_1.checkTrue(new form_1.Form($()) instanceof widget_1.Widget);
        });
        it('isRequiredEl()', function () {
            check_1.checkTrue(form_1.Form.isRequiredEl(Page.requireEl()));
            check_1.checkFalse(form_1.Form.isRequiredEl(Page.nonRequiredEl()));
        });
        it('els() non-empty form', function () {
            var form = new form_1.Form($('#non-empty-form'));
            check_1.checkLength(numberOfPossibleFormEls, form.els());
        });
        it('els() empty form', function () {
            check_1.checkEqual(0, Page.emptyForm().els().length);
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
        it('Default submit handler is not called', function (done) {
            var TestForm = (function (_super) {
                __extends(TestForm, _super);
                function TestForm() {
                    return _super !== null && _super.apply(this, arguments) || this;
                }
                TestForm.prototype.handleResponse = function (responseData) {
                    this.handleResponseCalled = true;
                };
                return TestForm;
            }(form_1.Form));
            var $form = Page.withRequiredElsFormEl();
            var form = new TestForm($form);
            form.skipValidation = true;
            $form.trigger('submit');
            var intervalId = setInterval(function () {
                if (form.handleResponseCalled) {
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
            function messageContainerEl() {
                return $form.find('.' + form.formMessageContainerCssClass);
            }
            check_1.checkTrue(form.hasErrors());
            check_1.checkEqual(messageText, messageContainerEl().text());
            form.clearErrors();
            check_1.checkNoEl(messageContainerEl());
            check_1.checkFalse(form.hasErrors());
        });
    });
});
