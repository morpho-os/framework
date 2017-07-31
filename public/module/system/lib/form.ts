import {ErrorMessage, renderMessage} from "./message";
import {redirectTo/*, tr*/} from "./bom";
import {Widget} from "./widget";

type ResponseErrorMessage = Pick<ErrorMessage, "text" | "args">;
export type ResponseError = ResponseErrorMessage[] | {[elName: string]: ResponseErrorMessage[]};

export class RequiredElValidator implements ElValidator {
    public static readonly EmptyValueMessage = 'This field is required';

    public validate($el: JQuery): string[] {
        if (Form.isRequiredEl($el)) {
            if (Form.elValue($el).trim().length < 1) {
                return [RequiredElValidator.EmptyValueMessage];
            }
        }
        return [];
    }
}

interface ElValidator {
    validate($el: JQuery): string[];
}

export function defaultValidators(): ElValidator[] {
    return [
        new RequiredElValidator()
    ];
}

export function validateEl($el: JQuery, validators?: ElValidator[]): string[] {
    if (!validators) {
        validators = defaultValidators();
    }
    let errors: string[] = [];
    validators.forEach(function (validator: ElValidator) {
        errors = errors.concat(validator.validate($el));
    });
    return errors;
}

export class Form extends Widget {
    public static readonly defaultInvalidCssClass = 'invalid';

    public skipValidation = false;

    public elContainerCssClass = 'form-group';
    public formMessageContainerCssClass = 'messages';
    public invalidCssClass = Form.defaultInvalidCssClass;

    public static elValue($el: JQuery): any {
        if ((<any>$el.get(0))['type'] === 'checkbox') {
            return $el.is(':checked') ? 1 : 0;
        }
        return $el.val();
    }

    public static isRequiredEl($el: JQuery): boolean {
        return $el.is('[required]');
    }

    public els(): JQuery {
        return $((<any> this.el[0]).elements);
    }

    public elsToValidate(): JQuery {
        return this.els().filter(function (this: JQuery) {
            const $el = $(this);
            return $el.is(':not(:submit)');//input[type=submit])') && $el.is(':not(button)');
        });
    }

    public validate(): boolean {
        this.clearErrors();
        let errors: Array<[JQuery, ErrorMessage[]]> = [];
        this.elsToValidate().each(function (this: HTMLFormElement) {
            const $el = $(this);
            const elErrors = validateEl($el);
            if (elErrors.length) {
                errors.push([$el, elErrors.map((error: string) => { return new ErrorMessage(error); })]);
            }
        });
        if (errors.length) {
            this.showErrors(errors);
            return false;
        }
        return true;
    }

    public invalidEls(): JQuery {
        const self = this;
        return this.els().filter(function (this: JQuery) {
            return $(this).hasClass(self.invalidCssClass);
        });
    }

    public hasErrors(): boolean {
        return this.el.hasClass(this.invalidCssClass);
    }

    public clearErrors(): void {
        this.invalidEls().each((index: number, el: HTMLFormElement) => {
            const $el = $(el);
            const $container = $el.removeClass(this.invalidCssClass).closest('.' + this.elContainerCssClass);
            if (!$container.find('.' + this.invalidCssClass).length) {
                $container.removeClass(this.invalidCssClass);
            }
            $el.next('.error').remove();
        });
        this.formMessageContainerEl().remove();
        this.el.removeClass(this.invalidCssClass);
    }

    public submit(): void {
        this.clearErrors();
        if (this.skipValidation) {
            this.send();
        } else if (this.validate()) {
            this.send();
        }
    }

    public send(): JQueryXHR {
        this.disableSubmitButtonEls();
        return this.sendFormData(this.uri(), this.formData());
    }

    /**
     * Displays either form errors or element errors or both.
     */
    public showErrors(errors: Array<ErrorMessage | [JQuery, ErrorMessage[]]>): void {
        let formErrors: ErrorMessage[] = [];
        errors.forEach((err: ErrorMessage | [JQuery, ErrorMessage[]]) => {
            if (Array.isArray(err)) {
                const [$el, elErrors] = err;
                this.showElErrors($el, elErrors);
            } else {
                formErrors.push(err);
            }
        });
        this.showFormErrors(formErrors);
        this.scrollToFirstError();
    }

    protected showFormErrors(errors: ErrorMessage[]): void {
        const rendered: string = '<div class="alert alert-error">' + errors.map(renderMessage).join("\n") + '</div>';
        this.formMessageContainerEl()
            .prepend(rendered);
        this.el.addClass(this.invalidCssClass);
    }

    protected showElErrors($el: JQuery, errors: ErrorMessage[]): void {
        const invalidCssClass = this.invalidCssClass;
        $el.addClass(invalidCssClass).closest('.' + this.elContainerCssClass).addClass(invalidCssClass);
        $el.after(errors.map(renderMessage).join("\n"));
    }

    protected formMessageContainerEl(): JQuery {
        const containerCssClass = this.formMessageContainerCssClass;
        let $containerEl = this.el.find('.' + containerCssClass);
        if (!$containerEl.length) {
            $containerEl = $('<div class="' + containerCssClass + '"></div>').prependTo(this.el);
        }
        return $containerEl;
    }

    protected init(): void {
        super.init();
        this.el.attr('novalidate', 'novalidate');
    }

    protected registerEventHandlers(): void {
        this.el.on('submit', () => {
            this.submit();
            return false;
        });
    }

    protected sendFormData(uri: string, requestData: Object): JQueryXHR {
        const ajaxSettings = this.ajaxSettings();
        ajaxSettings.url = uri;
        ajaxSettings.data = requestData;
        return $.ajax(ajaxSettings);
    }

    protected ajaxSettings(): JQueryAjaxSettings {
        const self = this;
        return {
            beforeSend(jqXHR: JQueryXHR, settings: JQueryAjaxSettings): any {
                return self.beforeSend(jqXHR, settings);
            },
            success(data: any, textStatus: string, jqXHR: JQueryXHR): any {
                return self.ajaxSuccess(data, textStatus, jqXHR);
            },
            error(jqXHR: JQueryXHR, textStatus: string, errorThrown: string): any {
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

    protected formData(): JQuerySerializeArrayElement[] {
        // @TODO: see the serializeArray() method: $('form').serializeArray()?
        const data: JQuerySerializeArrayElement[] = [];
        this.els().each((index, node) => {
            const name = node.getAttribute('name');
            if (!name) {
                return;
            }
            data.push({
                name,
                value: Form.elValue($(node))
            });
        });
        return data;
    }

    protected uri(): string {
        return this.el.attr('action') || (<any>window).location.href;
    }

    protected enableSubmitButtonEls(): void {
        this.submitButtonEls().prop('disabled', false);
    }

    protected disableSubmitButtonEls(): void {
        this.submitButtonEls().prop('disabled', true);
    }

    protected submitButtonEls(): JQuery {
        return this.els().filter(function (this: JQuery) {
            return $(this).is(':submit');
        });
    }

    protected handleResponse(responseData: JsonResponse): void {
        if (responseData.error) {
            this.handleResponseError(responseData.error);
        } else if (responseData.success) {
            this.handleResponseSuccess(responseData.success);
        } else {
            this.invalidResponseError();
        }
    }

    protected handleResponseSuccess(responseData: any): any {
        if (responseData.redirect) {
            redirectTo(responseData.redirect);
            return true;
        }
    }

    protected handleResponseError(responseData: ResponseError): void {
        if (Array.isArray(responseData)) {
            const errors = responseData.map((message: ResponseErrorMessage) => {
                return new ErrorMessage(message.text, message.args);
            });
            this.showErrors(errors);
        } else {
            this.invalidResponseError();
        }
    }

    protected invalidResponseError(): void {
        alert('Invalid response'); // @TODO
    }

    protected scrollToFirstError(): void {
        let $first = this.el.find('.error:first');
        let $container = $first.closest('.' + this.elContainerCssClass);
        if ($container.length) {
            $first = $container;
        } else {
            $container = $first.closest('.' + this.formMessageContainerCssClass);
            if ($container.length) {
                $first = $container;
            }
        }
        if (!$first.length) {
            return;
        }
        // @TODO: scroll to $first
    }
}
