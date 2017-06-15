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
define("system/app/install/index", ["require", "exports", "../../lib/form", "../../lib/system"], function (require, exports, form_1, system_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var InstallForm = (function (_super) {
        __extends(InstallForm, _super);
        function InstallForm() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        InstallForm.prototype.init = function () {
            this.dbNameEl().focus();
        };
        InstallForm.prototype.registerEventHandlers = function () {
            var _this = this;
            _super.prototype.registerEventHandlers.call(this);
            this.dbNameEl().on('keyup change blur', function () {
                _this.targetDbEl().text(_this.dbNameEl().val());
            });
        };
        InstallForm.prototype.dbNameEl = function () {
            return this.el.find('#db');
        };
        InstallForm.prototype.targetDbEl = function () {
            return this.el.find('#target-db');
        };
        InstallForm.prototype.handleResponseSuccess = function (responseData) {
            if (!responseData.redirect) {
                alert('Invalid response was received');
            }
            else {
                system_1.redirectToHome();
            }
        };
        InstallForm.prototype.handleResponseError = function (responseData) {
            alert('Error');
        };
        return InstallForm;
    }(form_1.Form));
    function main() {
        new InstallForm($('#install-form'));
    }
    exports.main = main;
});