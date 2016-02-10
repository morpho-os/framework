/// <reference path="../../../../public/module/system/src/d.ts/all.d.ts" />

namespace System.Install {
    import Form = System.Form;
    export class InstallForm extends Form {
        protected registerEventHandlers(): void {
            super.registerEventHandlers();
            this.getDbNameEl().on('keyup change blur', () => {
                this.setTargetDbElText(this.getDbName());
            });
        }

        private getDbNameEl(): JQuery {
            return this.el.find('#db');
        }

        private getDbName(): any {
            return this.getDbNameEl().val();
        }

        private setTargetDbElText(text: string): void {
            this.getTargetDbEl().text(text);
        }

        private getTargetDbEl(): JQuery {
            return this.el.find('#target-db');
        }

        protected handleResponseSuccess(responseData: any): void {
            if (!(<any>responseData).redirect) {
                alert('Invalid response was received');
            } else {
                System.redirectToHome();
            }
        }

        protected handleResponseError(responseData: any): void {
            alert('Error');
        }
    }
}