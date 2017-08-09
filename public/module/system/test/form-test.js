define("system/test/form-test", ["require", "exports", "../lib/form", "../lib/message", "../lib/check"], function (require, exports, form_1, message_1, check_1) {
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
        describe('Validation', function () {
            function checkNoFormErrors($form) {
                check_1.checkNoEl($form.find('.has-error'));
                check_1.checkNoEl($form.find('.error'));
                check_1.checkNoEl($form.find('.messages'));
                check_1.checkNoEl($form.find('.alert-error'));
            }
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
