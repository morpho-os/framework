import {Form} from "../../lib/form"
import {redirectToHome} from "../../lib/system";

class InstallForm extends Form {
    protected init(): void {
        this.dbNameEl().focus();
    }

    protected registerEventHandlers(): void {
        super.registerEventHandlers();
        this.dbNameEl().on('keyup change blur', () => {
            this.targetDbEl().text(this.dbNameEl().val());
        });
    }

    private dbNameEl(): JQuery {
        return this.el.find('#db');
    }

    private targetDbEl(): JQuery {
        return this.el.find('#target-db');
    }

    protected handleResponseSuccess(responseData: any): void {
        if (!(<any>responseData).redirect) {
            alert('Invalid response was received');
        } else {
            redirectToHome();
        }
    }

    protected handleResponseError(responseData: any): void {
        alert('Error');
    }
}

/*let installForm : Form;
function newInstallForm() {
    installForm = new InstallForm($('#install-form'));
}*/
export function main () {
    new InstallForm($('#install-form'));
    //newInstallForm();
}