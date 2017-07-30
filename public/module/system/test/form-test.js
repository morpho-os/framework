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
define("system/test/form-test", ["require", "exports", "../lib/form", "../lib/widget"], function (require, exports, form_1, widget_1) {
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
        Page.emptyForm = function () {
            return new form_1.Form(Page.emptyFormEl());
        };
        Page.withRequiredElsFormEl = function () {
            return Page.el('#with-required-el-form');
        };
        Page.emptyFormEl = function () {
            return Page.el('#empty-form');
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
                    $(this).removeClass(form_1.Form.invalidCssClass);
                    $(this).closest('.form-group').removeClass(form_1.Form.invalidCssClass);
                });
            });
            $form.find('.error').remove();
            $form.find('.alert').remove();
            $form.find('input[type=checkbox]').prop('checked', false);
            $form.find('.' + form_1.Form.invalidCssClass).addBack().removeClass(form_1.Form.invalidCssClass);
        });
        describe('Validation', function () {
            it('validateEl() required element', function () {
                var $el = Page.requireEl();
                var errors = form_1.validateEl($el);
                expect(errors.length).toEqual(1);
                expect(errors[0]).toEqual(form_1.RequiredElValidator.EmptyValueMessage);
            });
            it('validateEl() non-required element', function () {
                var $el = Page.nonRequiredEl();
                var errors = form_1.validateEl($el);
                expect(errors.length).toEqual(0);
            });
            it('elsToValidate() excludes submit buttons', function () {
                var $form = Page.el('#non-empty-form');
                var form = new form_1.Form($form);
                var $elsToValidate = form.elsToValidate();
                var i = 0;
                $elsToValidate.each(function () {
                    var $el = $(this);
                    expect($el.is(':submit')).toBeFalsy();
                    i++;
                });
                expect(i).toEqual(numberOfPossibleFormEls - 2);
            });
            it("validate() of the empty form", function () {
                var form = Page.emptyForm();
                expect(form.validate()).toBeTruthy();
            });
            it('validate() with required elements', function () {
                var $form = Page.withRequiredElsFormEl();
                var form = new form_1.Form($form);
                expect(form.hasValidationErrors()).toBeFalsy();
                expect(form.validate()).toBeFalsy();
                var $invalidEls = form.invalidEls();
                expect($invalidEls.get(0).tagName.toLowerCase()).toEqual('input');
                expect($invalidEls.eq(0).attr('type')).toEqual('text');
                expect($invalidEls.get(1).tagName.toLowerCase()).toEqual('textarea');
                var invalidCssClass = form_1.Form.invalidCssClass;
                var i = 0;
                $invalidEls.each(function () {
                    var $el = $(this);
                    expect($el.next().text()).toEqual(form_1.RequiredElValidator.EmptyValueMessage);
                    expect($el.hasClass(invalidCssClass)).toBeTruthy();
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
                var form = new form_1.Form(Page.withRequiredElsFormEl());
                expect(form.invalidEls().length).toEqual(0);
            });
            it('Has "novalidate" attribute', function () {
                var $el = Page.emptyFormEl();
                expect($el.attr('novalidate')).toBeUndefined();
                new form_1.Form($el);
                expect($el.attr('novalidate')).toEqual('novalidate');
            });
        });
        it('Is Widget', function () {
            expect(new form_1.Form($()) instanceof widget_1.Widget).toBeTruthy();
        });
        it('isRequiredEl()', function () {
            expect(form_1.Form.isRequiredEl(Page.requireEl())).toBeTruthy();
            expect(form_1.Form.isRequiredEl(Page.nonRequiredEl())).toBeFalsy();
        });
        it('els() non-empty form', function () {
            var form = new form_1.Form($('#non-empty-form'));
            expect(form.els().length).toEqual(numberOfPossibleFormEls);
        });
        it('els() empty form', function () {
            expect(Page.emptyForm().els().length).toEqual(0);
        });
        it('elValue()', function () {
            var $form = $('#with-values-form');
            expect(form_1.Form.elValue($form.find("[name='textBox']"))).toEqual('bar');
            var $checkbox = $form.find("[name='checkBox']");
            expect(form_1.Form.elValue($checkbox)).toEqual(0);
            $checkbox.prop('checked', true);
            expect(form_1.Form.elValue($checkbox)).toEqual(1);
        });
        it('send() - response errors', function (done) {
            var form = new form_1.Form($('#server-error-form'));
            expect(form.hasValidationErrors()).toBeFalsy();
            form.send()
                .then(function () {
                expect(form.hasValidationErrors()).toBeTruthy();
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
                expect(form.successHandlerArgs).toEqual([{ redirect: "/go/to/linux" }]);
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
                    expect(true).toBeTruthy(true);
                    done();
                }
            }, 200);
        });
    });
});
