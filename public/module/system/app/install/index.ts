/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
import {Form, ResponseError} from "../../lib/form";
import {redirectToHome} from "../../lib/bom";

class InstallForm extends Form {
    protected init(): void {
        this.dbNameEl().focus();
    }

    protected registerEventHandlers(): void {
        super.registerEventHandlers();
        this.dbNameEl().on('keyup blur change paste', () => {
            this.targetDbEl().text(this.dbNameEl().val() + '');
        });
    }

    protected handleResponseSuccess(responseData: any): void {
        if (!(<any>responseData).redirect) {
            alert('Invalid response was received');
        } else {
            redirectToHome();
        }
    }

    protected handleResponseError(responseData: ResponseError): void {
        alert('Error');
    }

    private dbNameEl(): JQuery {
        return this.el.find('#db');
    }

    private targetDbEl(): JQuery {
        return this.el.find('#target-db');
    }
}

/*let installForm : Form;
function newInstallForm() {
    installForm = new InstallForm($('#install-form'));
}*/
let form: Form;
export function main() {
    form = new InstallForm($('#install-form'));
    //newInstallForm();
}
