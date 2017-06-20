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
define("system/test/form-test", ["require", "exports", "../lib/form", "../lib/test-case"], function (require, exports, form_1, test_case_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var FormTest = (function (_super) {
        __extends(FormTest, _super);
        function FormTest() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        FormTest.prototype.testValidate_EmptyForm = function () {
            var form = new form_1.Form($('form:eq(0)'));
            this.assertFalse(form.wasValidated());
        };
        return FormTest;
    }(test_case_1.TestCase));
    exports.FormTest = FormTest;
    function tests() {
        return [
            new FormTest()
        ];
    }
    exports.tests = tests;
});
