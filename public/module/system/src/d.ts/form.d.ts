/// <reference path="widget.d.ts" />
/// <reference path="message.d.ts" />
/// <reference path="system.d.ts" />
/// <reference path="bom.d.ts" />
/// <reference path="error.d.ts" />

declare namespace System {
    class Form extends Widget {
        private _wasValidated;
        private _isValid;
        protected messages: {
            [type: number]: Message[];
        };
        wasAtLeastOnceValidated(): boolean;
        validate(): boolean;
        isValid(): boolean;
        getEls(): JQuery;
        getElsToValidate(): JQuery;
        getSubmitButtonEls(): JQuery;
        getInvalidEls(): JQuery;
        addFormMessage(message: Message): void;
        getFormMessages(type?: MessageType): Message[];
        showFormMessage(message: Message): void;
        showAddedFormMessages(): void;
        showAddedFormMessage(message: Message): void;
        showFormMessages(messages: Message[]): void;
        hasErrors(): boolean;
        clearErrors(): void;
        showFormErrorMessage(text: string): void;
        getFormErrorMessages(): Message[];
        addFormErrorMessage(text: string): void;
        protected init(): void;
        protected _showAddedFormMessage(message: Message): void;
        protected removeElsErrors(): void;
        protected removeElErrors($el: JQuery): void;
        protected removeFormErrors(): void;
        protected getMessageContainerEl(): JQuery;
        protected ensureIsAddedFormMessage(message: Message): void;
        protected isAddedFormMessage(message: Message): boolean;
        protected formatFormMessage(message: Message): string;
        protected _validate(): boolean;
        protected validateEls(): boolean;
        protected validateEl($el: JQuery): boolean;
        protected validateRequiredEl($el: JQuery): boolean;
        protected showValueRequiredElError($el: JQuery): void;
        protected showElMessage($el: JQuery, message: Message): void;
        protected showElError($el: JQuery, text: string): void;
        protected formatElMessage(message: Message): string;
        protected isRequiredEl($el: JQuery): boolean;
        protected registerEventHandlers(): void;
        protected registerSubmitEventHandler(): void;
        protected handleSubmit(): boolean;
        protected submit(): void;
        protected getUri(): string;
        protected getFormData(): Array<JQuerySerializeArrayElement>;
        protected getElValue($el: JQuery): any;
        protected sendFormData(uri: string, requestData: Object): JQueryXHR;
        protected sendAjaxRequest(ajaxSettings: JQueryAjaxSettings): JQueryXHR;
        protected getAjaxSettings(): JQueryAjaxSettings;
        protected beforeSend(jqXHR: JQueryXHR, settings: JQueryAjaxSettings): any;
        protected ajaxSuccess(responseData: any, textStatus: string, jqXHR: JQueryXHR): void;
        protected ajaxError(jqXHR: JQueryXHR, textStatus: string, errorThrown: string): void;
        protected enableSubmitButtonEls(): void;
        protected disableSubmitButtonEls(): void;
        protected handleResponse(responseData: JsonResponse): void;
        protected handleResponseSuccess(responseData: any): void;
        protected handleResponseError(responseData: any): void;
        protected changeEventNames(): string;
        protected showUnknownError(): void;
    }
    interface ResponseMessage {
        message: string;
        args?: Array<string>;
    }
    interface JsonResponse {
        error: any;
        success: any;
    }
}