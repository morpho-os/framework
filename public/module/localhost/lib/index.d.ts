/// <reference types="jquery" />
declare module "localhost/lib/event-manager" {
    export class EventManager {
        private eventHandlers;
        on(eventName: string, handler: (...args: any[]) => any): void;
        trigger(eventName: string, ...args: any[]): void;
    }
}
declare module "localhost/lib/widget" {
    import { EventManager } from "localhost/lib/event-manager";
    export interface WidgetConfig {
        el?: JQuery | string;
    }
    export abstract class Widget<TConfig extends WidgetConfig = WidgetConfig> extends EventManager {
        protected el: JQuery;
        protected config: TConfig;
        constructor(config: TConfig);
        protected init(): void;
        protected registerEventHandlers(): void;
        protected normalizeConfig(config: TConfig): TConfig;
    }
}
declare module "localhost/lib/message" {
    import { Widget } from "localhost/lib/widget";
    export enum MessageType {
        Error = 1,
        Warning = 2,
        Info = 4,
        Debug = 8,
        All = 15
    }
    export class PageMessenger extends Widget {
        protected numberOfMessages(): number;
        protected messageEls(): JQuery;
        protected registerEventHandlers(): void;
        protected registerCloseMessageHandler(): void;
    }
    export function renderMessage(message: Message): string;
    export function messageTypeToStr(type: MessageType): string;
    export class Message {
        type: MessageType;
        text: string;
        args: string[];
        constructor(type: MessageType, text: string, args?: string[]);
        hasType(type: MessageType): boolean;
    }
    export class ErrorMessage extends Message {
        constructor(text: string, args?: string[]);
    }
    export class WarningMessage extends Message {
        constructor(text: string, args?: string[]);
    }
    export class InfoMessage extends Message {
        constructor(text: string, args?: string[]);
    }
    export class DebugMessage extends Message {
        constructor(text: string, args?: string[]);
    }
}
declare module "localhost/lib/app" {
    export class App {
        context: Record<string, any>;
        constructor();
    }
    global {
        interface Window {
            app: App;
        }
    }
}
declare module "localhost/lib/base" {
    export function id(value: any): any;
    export function isPromise(value: any): boolean;
    export function isDomNode(obj: any): boolean;
    export function isGenerator(fn: Function): boolean;
    export class Re {
        static readonly email: RegExp;
    }
    export function showUnknownError(message?: string): void;
    export function redirectToSelf(): void;
    export function redirectToHome(): void;
    export function redirectTo(uri: string, storePageInHistory?: boolean): void;
    export function queryArgs(): JQuery.PlainObject;
    export function delayedCallback(callback: Function, waitMs: number): (this: any, ...args: any[]) => void;
}
declare module "localhost/lib/error" {
    export class Exception extends Error {
        message: string;
        constructor(message: string);
        toString(): string;
    }
    export class NotImplementedException extends Exception {
    }
    export class UnexpectedValueException extends Exception {
    }
}
declare module "localhost/lib/form" {
    import { ErrorMessage } from "localhost/lib/message";
    import { Widget } from "localhost/lib/widget";
    type ResponseErrorMessage = Pick<ErrorMessage, "text" | "args">;
    export type ResponseError = ResponseErrorMessage[] | {
        [elName: string]: ResponseErrorMessage[];
    };
    export class RequiredElValidator implements ElValidator {
        static readonly EmptyValueMessage = "This field is required";
        validate($el: JQuery): string[];
    }
    interface ElValidator {
        validate($el: JQuery): string[];
    }
    export function defaultValidators(): ElValidator[];
    export function validateEl($el: JQuery, validators?: ElValidator[]): string[];
    export class Form extends Widget {
        static readonly defaultInvalidCssClass: string;
        skipValidation: boolean;
        elContainerCssClass: string;
        formMessageContainerCssClass: string;
        invalidCssClass: string;
        protected elChangeEvents: string;
        static elValue($el: JQuery): any;
        static isRequiredEl($el: JQuery): boolean;
        els(): JQuery;
        elsToValidate(): JQuery;
        validate(): boolean;
        invalidEls(): JQuery;
        hasErrors(): boolean;
        removeErrors(): void;
        submit(): void;
        send(): JQueryXHR;
        showErrors(errors: Array<ErrorMessage | [JQuery, ErrorMessage[]]>): void;
        protected showFormErrors(errors: ErrorMessage[]): void;
        protected showElErrors($el: JQuery, errors: ErrorMessage[]): void;
        protected removeElErrors($el: JQuery): void;
        protected formMessageContainerEl(): JQuery;
        protected init(): void;
        protected registerEventHandlers(): void;
        protected sendFormData(uri: string, requestData: Object): JQueryXHR;
        protected ajaxSettings(): JQueryAjaxSettings;
        protected submitMethod(): string;
        protected beforeSend(jqXHR: JQueryXHR, settings: JQueryAjaxSettings): any;
        protected ajaxSuccess(responseData: any, textStatus: string, jqXHR: JQueryXHR): any;
        protected ajaxError(jqXHR: JQueryXHR, textStatus: string, errorThrown: string): any;
        protected formData(): JQuerySerializeArrayElement[];
        protected uri(): string;
        protected enableSubmitButtonEls(): void;
        protected disableSubmitButtonEls(): void;
        protected submitButtonEls(): JQuery;
        protected handleResponse(responseData: JsonResponse): void;
        protected handleResponseSuccess(responseData: any): any;
        protected handleResponseError(responseData: ResponseError): void;
        protected invalidResponseError(): void;
        protected scrollToFirstError(): void;
    }
}
declare module "localhost/lib/i18n" {
    export function tr(message: string): string;
}
interface RegExpConstructor {
    escape(s: string): string;
}

interface JQueryStatic {
    resolvedPromise(...args: any[]): JQueryPromise<any>;

    rejectedPromise(...args: any[]): JQueryPromise<any>;
}

interface JQuery {
    once(this: JQuery, fn: (key: any, value: any) => any): JQuery;
}

interface String {
    titleize(): string;
    nl2Br(): string;
    replaceAll(search: string, replace: string): string;
    escapeHtml(): string;
}

interface Math {
    EPS: number;

    // Returns x from base^x ~> n, e.g.: logN(8, 2) ~> 3, because 2^3 ~> 8
    logN(n: number, base: number): number;

    roundFloat(val: number, precision: number): number;

    floatLessThanZero(val: number): boolean;

    floatGreaterThanZero(val: number): boolean;

    floatEqualZero(val: number): boolean;

    floatsEqual(a: number, b: number): boolean;
}


interface JQuery {
    once(this: JQuery, fn: (key: any, value: any) => any): JQuery;
}

interface Math {
    EPS: number;
    roundFloat(val: number, precision: number): number;
    floatLessThanZero(val: number): boolean;
    floatGreaterThanZero(val: number): boolean;
    floatEqualZero(val: number): boolean;
    floatsEqual(a: number, b: number): boolean;
}

interface String {
    encodeHtml(): string;
    titleize(): string;
    format(args: string[], filter?: (s: string) => string): string;
}

interface JsonResponse {
    error: any;
    success: any;
}
