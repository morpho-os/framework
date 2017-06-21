define("system/test/form-test", ["require", "exports", "../lib/form"], function (require, exports, form_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    describe("Form", function () {
        var form;
        beforeEach(function () {
            form = new form_1.Form($('form:eq(0)'));
        });
        it("Validate of the empty form", function () {
            expect(form.wasValidated()).toBeFalsy();
        });
    });
});
