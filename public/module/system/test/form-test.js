/// <reference path="../src/d.ts/form.d.ts"/>
/// <reference path="../src/d.ts/test-case.d.ts"/>
/// <reference path="../src/d.ts/message.d.ts"/>
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var SystemTest;
(function (SystemTest) {
    var Form = System.Form;
    var TestCase = System.TestCase;
    var FormTest = (function (_super) {
        __extends(FormTest, _super);
        function FormTest() {
            _super.apply(this, arguments);
        }
        FormTest.prototype.testValidate_EmptyForm = function () {
            var form = new Form($('form:eq(0)'));
            this.assertFalse(form.wasValidated());
            this.assertTrue(form.validate());
            this.assertTrue(form.wasValidated());
            this.assertTrue(form.isValid());
        };
        FormTest.prototype.testValidate_RequiredElements = function () {
            var form = new Form($('form:eq(2)'));
            this.assertFalse(form.validate());
            var $invalidEls = form.getInvalidEls();
        };
        FormTest.prototype.testGetInvalidEls_BeforeValidation = function () {
            var form = new Form($('form:eq(2)'));
            this.assertEquals([], form.getInvalidEls());
        };
        return FormTest;
    }(TestCase));
    SystemTest.FormTest = FormTest;
})(SystemTest || (SystemTest = {}));
