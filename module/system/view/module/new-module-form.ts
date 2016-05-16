/// <reference path="../../../../public/module/system/src/d.ts/all.d.ts"/>

namespace Morpho.System.Module {
    export class NewModuleForm extends Morpho.System.Form {
        protected init(): void {
            this.fsTreeCheckboxEls().prop('checked', this.checkAllEl().prop('checked'));
        }

        protected registerEventHandlers(): void {
            super.registerEventHandlers();

            this.checkAllEl().click(() => {
                this.fsTreeCheckboxEls().prop('checked', this.checkAllEl().prop('checked'));
            });

            var self = this;
            this.fsTreeCheckboxEls().click(function () {
                var $checkbox = $(this);
                if (!$checkbox.prop('checked')) {
                    self.checkAllEl().prop('checked', false);
                } else {
                    var $fsTreeCheckboxEls = self.fsTreeCheckboxEls();
                    if ($fsTreeCheckboxEls.filter(':checked').length === $fsTreeCheckboxEls.length) {
                        self.checkAllEl().prop('checked', true);
                    }
                }
            });
        }

        protected fsTreeCheckboxEls(): JQuery {
            return this.fsTreeEl().find("input[type=checkbox]").not(this.checkAllEl());
        }

        protected fsTreeEl(): JQuery {
            return this.el.find('.fs-tree');
        }

        protected checkAllEl(): JQuery {
            return this.el.find('.check-all');
        }

        protected handleResponseSuccess(responseData: any): void {
            if (super.handleResponseSuccess(responseData)) {
                return;
            }
            //console.log(responseData);
        }
    }
}
/*
$(function () {
    $checkAll.click(function () {
    });
    $fsTreeCheckboxes.click(function () {
    });
    /*
    // @TODO: Handle parent-child check/uncheck.
    $newModuleForm.submit(function () {
        $.ajax({
            url: $newModuleForm.attr('action'),
            data: $newModuleForm.se
        });


        return false;
    });
});
*/
