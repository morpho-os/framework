/// <reference path="../../../../public/module/system/src/d.ts/main.d.ts" />
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var System;
(function (System) {
    var Install;
    (function (Install) {
        var Form = System.Form;
        var InstallForm = (function (_super) {
            __extends(InstallForm, _super);
            function InstallForm() {
                _super.apply(this, arguments);
            }
            InstallForm.prototype.registerEventHandlers = function () {
                var _this = this;
                _super.prototype.registerEventHandlers.call(this);
                this.getDbNameEl().on('keyup change blur', function () {
                    _this.setTargetDbElText(_this.getDbName());
                });
            };
            InstallForm.prototype.getDbNameEl = function () {
                return this.el.find('#db');
            };
            InstallForm.prototype.getDbName = function () {
                return this.getDbNameEl().val();
            };
            InstallForm.prototype.setTargetDbElText = function (text) {
                this.getTargetDbEl().text(text);
            };
            InstallForm.prototype.getTargetDbEl = function () {
                return this.el.find('#target-db');
            };
            InstallForm.prototype.handleResponseSuccess = function (responseData) {
                if (!responseData.redirect) {
                    alert('Invalid response was received');
                }
                else {
                    System.redirectToHome();
                }
            };
            InstallForm.prototype.handleResponseError = function (responseData) {
                alert('Error');
            };
            return InstallForm;
        })(Form);
        Install.InstallForm = InstallForm;
    })(Install = System.Install || (System.Install = {}));
})(System || (System = {}));
