/// <reference path="widget" />
/// <reference path="message" />
/// <reference path="system"/>
/// <reference path="bom"/>
/// <reference path="error"/>

/**
 * Definitions:
 * Error = FormError | ElError.
 * Element = JQuery object
 * FormError is common Error for 
 */
namespace System {
    export class Form extends Widget {
        private _wasValidated = false;
        private _isValid: boolean = null;
        protected messages: {[type: number]: Message[]} = {};

        public wasAtLeastOnceValidated(): boolean {
            return this._wasValidated;
        }

        public validate(): boolean {
            this._isValid = this._validate();
            this._wasValidated = true;
            return this._isValid;
        }

        public isValid(): boolean {
            if (!this.wasAtLeastOnceValidated()) {
                throw new Error("Unable to check state, the form should be validated first");
            }
            return this._isValid;
        }

        public getEls(): JQuery {
            return $((<any>this.el[0]).elements);
        }

        public getElsToValidate(): JQuery {
            return this.getEls().filter(function () {
                const $el = $(this);
                return $el.is(':not(input[type=submit])') && $el.is(':not(button)');
            });
        }

        public getSubmitButtonEls(): JQuery {
            return this.getEls().filter(function () {
                return $(this).is(':submit');
            });
        };

        public getInvalidEls(): JQuery {
            if (!this.wasAtLeastOnceValidated()) {
                return $();
            }
            return this.getEls().filter(function () {
                return $(this).hasClass('invalid');
            });
        }

        public addFormMessage(message: Message): void {
            var type = message.type;
            if (typeof this.messages[type] === 'undefined') {
                this.messages[type] = [];
            }
            this.messages[type].push(message);
        }

        public getFormMessages(type: MessageType = null): Message[] {
            var messages: Message[] = [],
                concatMessages = (type: MessageType) => {
                    if (typeof this.messages[type] !== 'undefined') {
                        messages = messages.concat(this.messages[type]);
                    }
                };
            if (null === type) {
                type = MessageType.All;
            }
            if (type & MessageType.Debug) {
                concatMessages(MessageType.Debug);
            }
            if (type & MessageType.Info) {
                concatMessages(MessageType.Info);
            }
            if (type & MessageType.Warning) {
                concatMessages(MessageType.Warning);
            }
            if (type & MessageType.Error) {
                concatMessages(MessageType.Error);
            }
            return messages;
        }

        public showFormMessage(message: Message): void {
            this.addFormMessage(message);
            this._showAddedFormMessage(message);
        }

        public showAddedFormMessages(): void {
            this.forEach(this.getFormErrorMessages, this.showAddedFormMessage);
        }

        public showAddedFormMessage(message: Message): void {
            this.ensureIsAddedFormMessage(message);
        }

        public showFormMessages(messages: Message[]): void {
            this.forEach(messages, this.showFormMessage);
        }

        public hasErrors(): boolean {
            return !this.isValid();
        }

        public clearErrors(): void {
            this.removeElsErrors();
            this.removeFormErrors();
            this.messages[MessageType.Error] = [];
        }

        public showFormErrorMessage(text: string): void {
            this.showFormMessage(new Message(MessageType.Error, text));
        }

        public getFormErrorMessages(): Message[] {
            return this.getFormMessages(MessageType.Error);
        }

        public addFormErrorMessage(text: string): void {
            this.addFormMessage(new Message(MessageType.Error, text));
        }

        protected init(): void {
            super.init();
            this.el.attr('novalidate', 'novalidate');
        }

        protected _showAddedFormMessage(message: Message): void {
            this.showEl(
                this.getMessageContainerEl()
                    .append(this.formatFormMessage(message))
            );
        }

        protected removeElsErrors(): void {
            this.forEachEl(this.getElsToValidate(), this.removeElErrors);
        }

        protected removeElErrors($el: JQuery): void {
            $el.closest('.form-group')
                .removeClass('has-error')
                .find('.error').remove();
            $el.removeClass('invalid');
        }

        protected removeFormErrors(): void {
            var $messageContainer = this.getMessageContainerEl();
            $messageContainer.find('.alert-error').remove();
            if ($messageContainer.is(':empty')) {
                this.hideEl($messageContainer);
            }
        }

        protected getMessageContainerEl(): JQuery {
            var containerCssClass = 'messages',
                $containerEl = this.el.find('.' + containerCssClass);
            if (!$containerEl.length) {
                $containerEl = $('<div class="' + containerCssClass + '"></div>').prependTo(this.el);
            }
            return $containerEl;
        }

        protected ensureIsAddedFormMessage(message: Message) {
            if (!this.isAddedFormMessage(message)) {
                throw new Error("Message must be added first");
            }
        }

        protected isAddedFormMessage(message: Message): boolean {
            return $.inArray(message, this.messages[message.type]) >= 0;
        }

        protected formatFormMessage(message: Message): string {
            if (!message.hasType(MessageType.Error)) {
                throw new NotImplementedException("formatMessage");
            }
            // @TODO: Decide where to escape message.text
            return '<div class="alert alert-error">' + message.text + '</div>';
        }
/*
        protected showUnknownError(message: string = null, context: any = null): void {
            this.showErrorMessage(message ? message : tr("Unknown error, please contact support"));
            this.notifyAboutError(message, context);
        }

        protected notifyAboutError(message: string = null, context: any = null): void {
            // @TODO: Send notification to server.
        }
*/
        protected _validate(): boolean {
            this.clearErrors();
            return this.validateEls();
        }

        protected validateEls(): boolean {
            var isValid = true;
            this.forEachEl(this.getElsToValidate(), ($el: JQuery) => {
                if (!this.validateEl($el)) {
                    isValid = false;
                }
            });
            return isValid;
        }

        protected validateEl($el: JQuery): boolean {
            this.removeElErrors($el);
            if (this.isRequiredEl($el)) {
                return this.validateRequiredEl($el);
            }
            return true;
        }

        protected validateRequiredEl($el: JQuery): boolean {
            var val = $el.val().trim();
            if (!val.length) {
                $el.addClass('invalid');
                this.showValueRequiredElError($el);
                return false;
            }
            return true;
        }

        protected showValueRequiredElError($el: JQuery): void {
            this.showElMessage($el, new Message(MessageType.Error, tr('This field is required')));
        }

        protected showElMessage($el: JQuery, message: Message): void {
            $el.after(this.formatElMessage(message));
            $el.closest('.form-group').addClass('has-error');
        }

        protected showElError($el: JQuery, text: string): void {
            this.showElMessage($el, new Message(MessageType.Error, text));
        }

        protected formatElMessage(message: Message): string {
            // @TODO: Decide where to escape message.text
            return '<div class="' + message.typeToString() + '">' + message.text + '</div>';
        }

        protected isRequiredEl($el: JQuery): boolean {
            return $el.is('[required]');
        }

        protected registerEventHandlers(): void {
            this.registerSubmitEventHandler();
        }

        protected registerSubmitEventHandler(): void {
            this.el.on('submit', this.handleSubmit.bind(this));
        }

        protected handleSubmit(): boolean {
            this.clearErrors();
            if (this.validate()) {
                this.submit();
            }
            return false;
        }

        protected submit(): void {
            this.disableSubmitButtonEls();
            this.sendFormData(this.getUri(), this.getFormData());
        }

        protected getUri(): string {
            return this.el.attr('action') || (<any>window).location.href;
        }

        protected getFormData(): Array<JQuerySerializeArrayElement> {
            // @TODO: see the serializeArray() method: $('form').serializeArray()?
            var data: Array<JQuerySerializeArrayElement> = [];
            this.getEls().each((index, node) => {
                var name = node.getAttribute('name');
                if (!name) {
                    return;
                }
                data.push({
                    name: name,
                    value: this.getElValue($(node))
                });
            });
            return data;
        }

        protected getElValue($el: JQuery): any {
            if ((<any>$el.get(0))['type'] == 'checkbox') {
                return $el.is(':checked') ? 1 : 0;
            }
            return $el.val();
        }

        protected sendFormData(uri: string, requestData: Object): JQueryXHR {
            var ajaxSettings = this.ajaxSettings();
            ajaxSettings.url = uri;
            ajaxSettings.data = requestData;
            return this.sendAjaxRequest(ajaxSettings);
        }

        protected sendAjaxRequest(ajaxSettings: JQueryAjaxSettings): JQueryXHR {
            return $.ajax(ajaxSettings);
        }

        protected ajaxSettings(): JQueryAjaxSettings {
            var self = this;
            return {
                beforeSend: function (jqXHR: JQueryXHR, settings: JQueryAjaxSettings): any {
                    return self.beforeSend(jqXHR, settings);
                },
                success: function (data: any, textStatus: string, jqXHR: JQueryXHR): any {
                    return self.ajaxSuccess(data, textStatus, jqXHR);
                },
                error: function (jqXHR: JQueryXHR, textStatus: string, errorThrown: string): any {
                    return self.ajaxError(jqXHR, textStatus, errorThrown);
                },
                method: this.submitMethod()
            };
        }

        protected submitMethod(): string {
            return this.el.attr('method') || 'GET';
        }

        protected beforeSend(jqXHR: JQueryXHR, settings: JQueryAjaxSettings): any {
        }

        protected ajaxSuccess(responseData: any, textStatus: string, jqXHR: JQueryXHR): any {
            this.enableSubmitButtonEls();
            this.handleResponse(responseData);
        }

        protected ajaxError(jqXHR: JQueryXHR, textStatus: string, errorThrown: string): any {
            this.enableSubmitButtonEls();
            // @TODO: Replace alert with internal method call.
            alert("AJAX error");
        }

        protected enableSubmitButtonEls() {
            this.getSubmitButtonEls().prop('disabled', false);
        }

        protected disableSubmitButtonEls() {
            this.getSubmitButtonEls().prop('disabled', true);
        }

        protected handleResponse(responseData: JsonResponse): void {
            if (responseData.error) {
                this.handleResponseError(responseData.error);
            } else if (responseData.success) {
                this.handleResponseSuccess(responseData.success);
            } else {
                this.showUnknownError();
            }
        }

        protected handleResponseSuccess(responseData: any): any {
            if (responseData.redirect) {
                redirectTo(responseData.redirect);
                return true;
            }
        }

        protected handleResponseError(responseData: any): any {
            this.showFormErrorMessage(responseData);
        }

        protected changeEventNames(): string {
            return 'keyup blur change paste';
        }

        protected showUnknownError(): void {
            showUnknownError(null);
        }
    }

    export interface ResponseMessage {
        message: string;
        args?: Array<string>;
    }
    export interface JsonResponse {
        error: any;
        success: any;
    }
}