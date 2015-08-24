class InstallForm extends Form {
    protected _registerEventHandlers(): void {
        super._registerEventHandlers();
        this._getDbNameEl().on('keyup change blur', () => {
            this._setTargetDbElText(this._getDbName());
        });
    }

    private _getDbNameEl(): JQuery {
        return this._el.find('#db');
    }

    private _getDbName(): any {
        return this._getDbNameEl().val();
    }

    private _setTargetDbElText(text: string): void {
        this._getTargetDbEl().text(text);
    }

    private _getTargetDbEl(): JQuery {
        return this._el.find('#target-db');
    }

    protected _handleSuccessfulResponse(responseData: JsonResponse): void {
        if (!(<any>responseData).redirect) {
            alert('Invalid response was received');
        } else {
            redirectToHome();
        }
    }
}