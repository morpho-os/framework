"use strict";
describe('Extension of the "BOM/Browser Object Model"', function () {
    describe('Math float', function () {
        it("Math.roundFloat", () => expect(Math.floatsEqual(Math.roundFloat(Math.PI, 2), 3.14)).toBeTruthy());
        it("Math.floatLessThanZero", () => expect(Math.floatLessThanZero(-0.0001)).toBeTruthy());
        it("Math.floatLessThanZero", () => expect(Math.floatLessThanZero(0)).toBeFalsy());
        it("Math.floatLessThanZero", () => expect(Math.floatLessThanZero(0.0001)).toBeFalsy());
        it("Math.isFloatGreaterThanZero", () => expect(Math.floatGreaterThanZero(0.0001)).toBeTruthy());
        it("Math.isFloatGreaterThanZero", () => expect(Math.floatGreaterThanZero(0)).toBeFalsy());
        it("Math.isFloatGreaterThanZero", () => expect(Math.floatGreaterThanZero(-0.0001)).toBeFalsy());
        it("Math.isFloatEqualZero", () => expect(Math.floatEqualZero(0)).toBeTruthy());
        it("Math.isFloatEqualZero", () => expect(Math.floatEqualZero(0.0001)).toBeFalsy());
        it("Math.isFloatEqualZero", () => expect(Math.floatEqualZero(-0.0001)).toBeFalsy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(0, 0)).toBeTruthy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(Math.PI, Math.PI)).toBeTruthy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(Math.PI, -Math.PI)).toBeFalsy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(-Math.PI, -Math.PI)).toBeTruthy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(-Math.PI, Math.PI)).toBeFalsy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(0, -0.0001)).toBeFalsy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(0, 0.0001)).toBeFalsy());
    });
});
define("localhost/lib/base/event-manager", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.EventManager = void 0;
    class EventManager {
        constructor() {
            Object.defineProperty(this, "handlers", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: {}
            });
        }
        on(eventName, handler) {
            this.handlers[eventName] = this.handlers[eventName] || [];
            this.handlers[eventName].push(handler);
        }
        trigger(eventName, ...args) {
            let handlers = this.handlers[eventName];
            if (!handlers) {
                return;
            }
            for (let i = 0; i < handlers.length; ++i) {
                if (false === handlers[i](...args)) {
                    break;
                }
            }
        }
    }
    exports.EventManager = EventManager;
});
define("localhost/lib/base/widget", ["require", "exports", "localhost/lib/base/event-manager"], function (require, exports, event_manager_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.errorToast = exports.okToast = exports.Widget = void 0;
    class Widget extends event_manager_1.EventManager {
        constructor(conf) {
            super();
            Object.defineProperty(this, "el", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "conf", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            this.conf = this.normalizeConf(conf);
            this.init();
            this.bindHandlers();
        }
        init() {
            if (this.conf && this.conf.el) {
                this.el = $(this.conf.el);
            }
        }
        bindHandlers() {
        }
        normalizeConf(conf) {
            return conf;
        }
    }
    exports.Widget = Widget;
    function okToast(text) {
        Toastify({
            text: text,
            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
            className: "info",
        }).showToast();
    }
    exports.okToast = okToast;
    function errorToast(text = null) {
        Toastify({
            text: text || 'Error',
            backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
            className: "info",
        }).showToast();
    }
    exports.errorToast = errorToast;
});
define("localhost/lib/base/message", ["require", "exports", "localhost/lib/base/widget"], function (require, exports, widget_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DebugMessage = exports.InfoMessage = exports.WarningMessage = exports.ErrorMessage = exports.Message = exports.messageTypeToStr = exports.renderMessage = exports.PageMessenger = exports.MessageType = void 0;
    var MessageType;
    (function (MessageType) {
        MessageType[MessageType["Error"] = 1] = "Error";
        MessageType[MessageType["Warning"] = 2] = "Warning";
        MessageType[MessageType["Info"] = 4] = "Info";
        MessageType[MessageType["Debug"] = 8] = "Debug";
        MessageType[MessageType["All"] = 15] = "All";
    })(MessageType = exports.MessageType || (exports.MessageType = {}));
    class PageMessenger extends widget_1.Widget {
        numberOfMessages() {
            return this.messageEls().length;
        }
        messageEls() {
            return this.el.find('.alert');
        }
        bindHandlers() {
            super.bindHandlers();
            this.registerCloseMessageHandler();
        }
        registerCloseMessageHandler() {
            const self = this;
            function hideElWithAnim($el, complete) {
                $el.fadeOut(complete);
            }
            function hideMainContainerWithAnim() {
                hideElWithAnim(self.el, function () {
                    self.el.find('.messages').remove();
                    self.el.hide();
                });
            }
            function closeMessageWithAnim($message) {
                if (self.numberOfMessages() === 1) {
                    hideMainContainerWithAnim();
                }
                else {
                    const $messageContainer = $message.closest('.messages');
                    if ($messageContainer.find('.alert').length === 1) {
                        hideElWithAnim($messageContainer, function () {
                            $messageContainer.remove();
                        });
                    }
                    else {
                        hideElWithAnim($message, function () {
                            $message.remove();
                        });
                    }
                }
            }
            this.el.on('click', 'button.close', function () {
                closeMessageWithAnim($(this).closest('.alert'));
            });
            setTimeout(function () {
                hideMainContainerWithAnim();
            }, 5000);
        }
    }
    exports.PageMessenger = PageMessenger;
    function renderMessage(message) {
        let text = message.text.encodeHtml();
        text = text.format(message.args);
        return wrapMessage(text, messageTypeToStr(message.type));
    }
    exports.renderMessage = renderMessage;
    function wrapMessage(text, type) {
        return '<div class="' + type.toLowerCase().encodeHtml() + '">' + text + '</div>';
    }
    function messageTypeToStr(type) {
        return MessageType[type];
    }
    exports.messageTypeToStr = messageTypeToStr;
    class Message {
        constructor(type, text, args = []) {
            Object.defineProperty(this, "type", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: type
            });
            Object.defineProperty(this, "text", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: text
            });
            Object.defineProperty(this, "args", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: args
            });
        }
        hasType(type) {
            return this.type === type;
        }
    }
    exports.Message = Message;
    class ErrorMessage extends Message {
        constructor(text, args = []) {
            super(MessageType.Error, text, args);
        }
    }
    exports.ErrorMessage = ErrorMessage;
    class WarningMessage extends Message {
        constructor(text, args = []) {
            super(MessageType.Warning, text, args);
        }
    }
    exports.WarningMessage = WarningMessage;
    class InfoMessage extends Message {
        constructor(text, args = []) {
            super(MessageType.Warning, text, args);
        }
    }
    exports.InfoMessage = InfoMessage;
    class DebugMessage extends Message {
        constructor(text, args = []) {
            super(MessageType.Debug, text, args);
        }
    }
    exports.DebugMessage = DebugMessage;
});
define("localhost/lib/base/base", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isErr = exports.delayedCallback = exports.queryArgs = exports.redirectTo = exports.redirectToHome = exports.redirectToSelf = exports.showUnknownError = exports.Re = exports.isGenerator = exports.isDomNode = exports.isPromise = exports.id = void 0;
    function id(value) {
        return value;
    }
    exports.id = id;
    function isPromise(val) {
        return val && typeof val.promise === 'function';
    }
    exports.isPromise = isPromise;
    function isDomNode(obj) {
        return obj.nodeType > 0;
    }
    exports.isDomNode = isDomNode;
    function isGenerator(fn) {
        return fn.constructor.name === 'GeneratorFunction';
    }
    exports.isGenerator = isGenerator;
    class Re {
    }
    exports.Re = Re;
    Object.defineProperty(Re, "email", {
        enumerable: true,
        configurable: true,
        writable: true,
        value: /^[^@]+@[^@]+$/
    });
    function showUnknownError(message) {
        alert("Unknown error, please contact support");
    }
    exports.showUnknownError = showUnknownError;
    function redirectToSelf() {
        window.location.reload();
    }
    exports.redirectToSelf = redirectToSelf;
    function redirectToHome() {
        redirectTo('/');
    }
    exports.redirectToHome = redirectToHome;
    function redirectTo(uri, storePageInHistory = true) {
        if (storePageInHistory) {
            window.location.href = uri;
        }
        else {
            window.location.replace(uri);
        }
    }
    exports.redirectTo = redirectTo;
    function queryArgs() {
        const decode = (input) => decodeURIComponent(input.replace(/\+/g, ' '));
        const parser = /([^=?&]+)=?([^&]*)/g;
        let queryArgs = {}, part;
        while (part = parser.exec(window.location.search)) {
            let key = decode(part[1]), value = decode(part[2]);
            if (key in queryArgs) {
                continue;
            }
            queryArgs[key] = value;
        }
        return queryArgs;
    }
    exports.queryArgs = queryArgs;
    function delayedCallback(callback, waitMs) {
        let timer = 0;
        return function () {
            const self = this;
            const args = arguments;
            clearTimeout(timer);
            timer = window.setTimeout(function () {
                callback.apply(self, args);
            }, waitMs);
        };
    }
    exports.delayedCallback = delayedCallback;
    function isErr(response) {
        return !response.ok;
    }
    exports.isErr = isErr;
});
define("localhost/lib/base/form", ["require", "exports", "localhost/lib/base/message", "localhost/lib/base/widget", "localhost/lib/base/base"], function (require, exports, message_1, widget_2, base_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Form = exports.elChangeEvents = exports.FieldType = exports.els = exports.forEachEl = exports.formData = exports.validateEl = exports.defaultValidators = exports.RequiredElValidator = void 0;
    class RequiredElValidator {
        validate($el) {
            if (Form.isRequiredEl($el)) {
                if (Form.elValue($el).trim().length < 1) {
                    return [RequiredElValidator.EmptyValueMessage];
                }
            }
            return [];
        }
    }
    exports.RequiredElValidator = RequiredElValidator;
    Object.defineProperty(RequiredElValidator, "EmptyValueMessage", {
        enumerable: true,
        configurable: true,
        writable: true,
        value: 'This field is required'
    });
    function defaultValidators() {
        return [
            new RequiredElValidator()
        ];
    }
    exports.defaultValidators = defaultValidators;
    function validateEl($el, validators) {
        if (!validators) {
            validators = defaultValidators();
        }
        let errors = [];
        validators.forEach(function (validator) {
            errors = errors.concat(validator.validate($el));
        });
        return errors;
    }
    exports.validateEl = validateEl;
    function formData($form) {
        const data = [];
        els($form).each((index, node) => {
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
    exports.formData = formData;
    function forEachEl($form, fn) {
        return els($form).each(function (index, el) {
            if (false === fn($(el), index)) {
                return false;
            }
            return undefined;
        });
    }
    exports.forEachEl = forEachEl;
    function els($form) {
        return $($form[0].elements);
    }
    exports.els = els;
    var FieldType;
    (function (FieldType) {
        FieldType["Button"] = "button";
        FieldType["Checkbox"] = "checkbox";
        FieldType["File"] = "file";
        FieldType["Hidden"] = "hidden";
        FieldType["Image"] = "image";
        FieldType["Password"] = "password";
        FieldType["Radio"] = "radio";
        FieldType["Reset"] = "reset";
        FieldType["Select"] = "select";
        FieldType["Submit"] = "submit";
        FieldType["Textarea"] = "textarea";
        FieldType["Textfield"] = "text";
    })(FieldType = exports.FieldType || (exports.FieldType = {}));
    exports.elChangeEvents = 'keyup blur change paste cut';
    class Form extends widget_2.Widget {
        constructor() {
            super(...arguments);
            Object.defineProperty(this, "skipValidation", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "elContainerCssClass", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "formMessageContainerCssClass", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "invalidCssClass", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "elChangeEvents", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
        }
        static elValue($el) {
            if ($el.get(0)['type'] === 'checkbox') {
                return $el.is(':checked') ? 1 : 0;
            }
            return $el.val();
        }
        static isRequiredEl($el) {
            return $el.is('[required]');
        }
        els() {
            return els(this.el);
        }
        elsToValidate() {
            return this.els().filter(function () {
                const $el = $(this);
                return $el.is(':not(:submit)');
            });
        }
        validate() {
            this.removeErrors();
            let errors = [];
            this.elsToValidate().each(function () {
                const $el = $(this);
                const elErrors = validateEl($el);
                if (elErrors.length) {
                    errors.push([$el, elErrors.map((error) => { return new message_1.ErrorMessage(error); })]);
                }
            });
            if (errors.length) {
                this.showErrors(errors);
                return false;
            }
            return true;
        }
        invalidEls() {
            const self = this;
            return this.els().filter(function () {
                return $(this).hasClass(self.invalidCssClass);
            });
        }
        hasErrors() {
            return this.el.hasClass(this.invalidCssClass);
        }
        removeErrors() {
            this.invalidEls().each((index, el) => {
                this.removeElErrors($(el));
            });
            this.formMessageContainerEl().remove();
            this.el.removeClass(this.invalidCssClass);
        }
        submit() {
            this.removeErrors();
            if (this.skipValidation) {
                this.send();
            }
            else if (this.validate()) {
                this.send();
            }
        }
        send() {
            this.disableSubmitButtonEls();
            return this.sendFormData(this.uri(), this.formData());
        }
        showErrors(errors) {
            let formErrors = [];
            errors.forEach((err) => {
                if (Array.isArray(err)) {
                    const [$el, elErrors] = err;
                    this.showElErrors($el, elErrors);
                }
                else {
                    formErrors.push(err);
                }
            });
            this.showFormErrors(formErrors);
            this.scrollToFirstError();
        }
        static fieldType($field) {
            const typeAttr = () => {
                const typeAttr = $field.attr('type');
                return typeAttr === undefined ? '' : typeAttr.toLowerCase();
            };
            let typeAttribute;
            switch ($field[0].tagName) {
                case 'INPUT':
                    typeAttribute = typeAttr();
                    switch (typeAttribute) {
                        case 'text':
                            return FieldType.Textfield;
                        case 'radio':
                            return FieldType.Radio;
                        case 'submit':
                            return FieldType.Submit;
                        case 'button':
                            return FieldType.Button;
                        case 'checkbox':
                            return FieldType.Checkbox;
                        case 'file':
                            return FieldType.File;
                        case 'hidden':
                            return FieldType.Hidden;
                        case 'image':
                            return FieldType.Image;
                        case 'password':
                            return FieldType.Password;
                        case 'reset':
                            return FieldType.Reset;
                    }
                    break;
                case 'TEXTAREA':
                    return FieldType.Textarea;
                case 'SELECT':
                    return FieldType.Select;
                case 'BUTTON':
                    typeAttribute = typeAttr();
                    if (typeAttribute === '' || typeAttribute === 'submit') {
                        return FieldType.Submit;
                    }
                    if (typeAttribute === 'button') {
                        return FieldType.Button;
                    }
                    break;
            }
            throw new Error('Unknown field type');
        }
        showFormErrors(errors) {
            if (errors.length) {
                const rendered = '<div class="alert alert-error">' + errors.map(message_1.renderMessage).join("\n") + '</div>';
                this.formMessageContainerEl()
                    .prepend(rendered);
            }
            this.el.addClass(this.invalidCssClass);
        }
        showElErrors($el, errors) {
            const invalidCssClass = this.invalidCssClass;
            $el.addClass(invalidCssClass).closest('.' + this.elContainerCssClass).addClass(invalidCssClass).addClass('has-error');
            $el.after(errors.map(message_1.renderMessage).join("\n"));
        }
        removeElErrors($el) {
            const $container = $el.removeClass(this.invalidCssClass).closest('.' + this.elContainerCssClass);
            if (!$container.find('.' + this.invalidCssClass).length) {
                $container.removeClass(this.invalidCssClass).removeClass('has-error');
            }
            $el.next('.error').remove();
        }
        formMessageContainerEl() {
            const containerCssClass = this.formMessageContainerCssClass;
            let $containerEl = this.el.find('.' + containerCssClass);
            if (!$containerEl.length) {
                $containerEl = $('<div class="' + containerCssClass + '"></div>').prependTo(this.el);
            }
            return $containerEl;
        }
        init() {
            super.init();
            this.skipValidation = false;
            this.elContainerCssClass = 'form-group';
            this.formMessageContainerCssClass = 'messages';
            this.invalidCssClass = Form.defaultInvalidCssClass;
            this.elChangeEvents = exports.elChangeEvents;
            this.el.attr('novalidate', 'novalidate');
        }
        bindHandlers() {
            this.el.on('submit', () => {
                this.submit();
                return false;
            });
            const self = this;
            this.elsToValidate().on(this.elChangeEvents, function () {
                const $el = $(this);
                if ($el.hasClass(self.invalidCssClass)) {
                    self.removeElErrors($el);
                }
            });
        }
        sendFormData(uri, requestData) {
            const ajaxSettings = this.ajaxSettings();
            ajaxSettings.url = uri;
            ajaxSettings.data = requestData;
            return $.ajax(ajaxSettings);
        }
        ajaxSettings() {
            const self = this;
            return {
                beforeSend(jqXHR, settings) {
                    return self.beforeSend(jqXHR, settings);
                },
                success(data, textStatus, jqXHR) {
                    return self.ajaxSuccess(data, textStatus, jqXHR);
                },
                error(jqXHR, textStatus, errorThrown) {
                    return self.ajaxError(jqXHR, textStatus, errorThrown);
                },
                method: this.submitMethod()
            };
        }
        submitMethod() {
            return this.el.attr('method') || 'GET';
        }
        beforeSend(jqXHR, settings) {
        }
        ajaxSuccess(responseData, textStatus, jqXHR) {
            this.enableSubmitButtonEls();
            this.handleResponse(responseData);
        }
        ajaxError(jqXHR, textStatus, errorThrown) {
            this.enableSubmitButtonEls();
            alert("AJAX error");
        }
        formData() {
            return formData(this.el);
        }
        uri() {
            return this.el.attr('action') || window.location.href;
        }
        enableSubmitButtonEls() {
            this.submitButtonEls().prop('disabled', false);
        }
        disableSubmitButtonEls() {
            this.submitButtonEls().prop('disabled', true);
        }
        submitButtonEls() {
            return this.els().filter(function () {
                return $(this).is(':submit');
            });
        }
        handleResponse(result) {
            if (result.err !== undefined) {
                this.handleErrResponse(result.err);
            }
            else if (result.ok !== undefined) {
                this.handleOkResponse(result.ok);
            }
            else {
                this.invalidResponseError();
            }
        }
        handleOkResponse(responseData) {
            if (responseData && responseData.redirect) {
                base_1.redirectTo(responseData.redirect);
                return true;
            }
        }
        handleErrResponse(responseData) {
            if (Array.isArray(responseData)) {
                const errors = responseData.map((message) => {
                    return new message_1.ErrorMessage(message.text, message.args);
                });
                this.showErrors(errors);
            }
            else {
                this.invalidResponseError();
            }
        }
        invalidResponseError() {
            alert('Invalid response');
        }
        scrollToFirstError() {
            let $first = this.el.find('.error:first');
            let $container = $first.closest('.' + this.elContainerCssClass);
            if ($container.length) {
                $first = $container;
            }
            else {
                $container = $first.closest('.' + this.formMessageContainerCssClass);
                if ($container.length) {
                    $first = $container;
                }
            }
            if (!$first.length) {
                return;
            }
        }
    }
    exports.Form = Form;
    Object.defineProperty(Form, "defaultInvalidCssClass", {
        enumerable: true,
        configurable: true,
        writable: true,
        value: 'invalid'
    });
});
define("localhost/lib/test/check", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.checkTrue = exports.checkFalse = exports.checkLength = exports.checkNoEl = exports.checkEmpty = exports.checkEqual = void 0;
    function checkEqual(expected, actual) {
        expect(actual).toEqual(expected);
    }
    exports.checkEqual = checkEqual;
    function checkEmpty(arr) {
        checkLength(0, arr);
    }
    exports.checkEmpty = checkEmpty;
    function checkNoEl($el) {
        checkLength(0, $el);
    }
    exports.checkNoEl = checkNoEl;
    function checkLength(expectedLength, list) {
        checkEqual(expectedLength, list.length);
    }
    exports.checkLength = checkLength;
    function checkFalse(actual) {
        expect(actual).toBeFalsy();
    }
    exports.checkFalse = checkFalse;
    function checkTrue(actual) {
        expect(actual).toBeTruthy();
    }
    exports.checkTrue = checkTrue;
});
define("localhost/test/form-test", ["require", "exports", "localhost/lib/base/form", "localhost/lib/base/widget", "localhost/lib/base/message", "localhost/lib/test/check"], function (require, exports, form_1, widget_3, message_2, check_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    class Page {
        static requireEl() {
            return Page.el("#with-required-el-form [type=text]");
        }
        static nonRequiredEl() {
            return Page.el("#with-values-form [name=textBox]");
        }
        static el(selector) {
            const $el = $(selector);
            if (!$el.length) {
                throw new Error();
            }
            return $el;
        }
        static emptyFormEl() {
            return Page.el('#empty-form');
        }
        static emptyForm() {
            return new form_1.Form({ el: Page.emptyFormEl() });
        }
        static withRequiredElsFormEl() {
            return Page.el('#with-required-el-form');
        }
        static withRequiredElsForm() {
            return new form_1.Form({ el: Page.withRequiredElsFormEl() });
        }
    }
    Object.defineProperty(Page, "numberOfElsOfNonEmptyForm", {
        enumerable: true,
        configurable: true,
        writable: true,
        value: 26
    });
    describe("Form", function () {
        afterEach(function () {
            const $form = $('form');
            $form.removeAttr('novalidate');
            $form.find('.' + form_1.Form.defaultInvalidCssClass).addBack().removeClass(form_1.Form.defaultInvalidCssClass);
            $form.find('.has-error').removeClass('has-error');
            $form.find('.error')
                .add($form.find('.alert'))
                .add($form.find('.messages'))
                .remove();
            $form.find('input[type=checkbox]').prop('checked', false);
        });
        describe('Validation', function () {
            function checkNoFormErrors($form) {
                check_1.checkNoEl($form.find('.has-error'));
                check_1.checkNoEl($form.find('.error'));
                check_1.checkNoEl($form.find('.messages'));
                check_1.checkNoEl($form.find('.alert-error'));
            }
            it('validateEl() required element', function () {
                const $el = Page.requireEl();
                const errors = form_1.validateEl($el);
                check_1.checkLength(1, errors);
                check_1.checkEqual(errors[0], form_1.RequiredElValidator.EmptyValueMessage);
            });
            it('validateEl() non-required element', function () {
                const $el = Page.nonRequiredEl();
                const errors = form_1.validateEl($el);
                check_1.checkEmpty(errors);
            });
            it('elsToValidate() excludes submit buttons', function () {
                const $form = Page.el('#non-empty-form');
                const form = new form_1.Form({ el: $form });
                const $elsToValidate = form.elsToValidate();
                let i = 0;
                $elsToValidate.each(function () {
                    const $el = $(this);
                    check_1.checkFalse($el.is(':submit'));
                    i++;
                });
                check_1.checkEqual(Page.numberOfElsOfNonEmptyForm - 2, i);
            });
            it("validate() of the empty form", function () {
                const $form = Page.emptyFormEl();
                const form = new form_1.Form({ el: $form });
                check_1.checkTrue(form.validate());
                checkNoFormErrors($form);
            });
            it('validate() with required elements', function () {
                const $form = Page.withRequiredElsFormEl();
                const form = new form_1.Form({ el: $form });
                check_1.checkFalse(form.hasErrors());
                check_1.checkFalse(form.validate());
                check_1.checkNoEl($form.find('.messages'));
                check_1.checkNoEl($form.find('.alert-error'));
                const $invalidEls = form.invalidEls();
                check_1.checkEqual('input', $invalidEls.get(0).tagName.toLowerCase());
                check_1.checkEqual('text', $invalidEls.eq(0).attr('type'));
                check_1.checkEqual('textarea', $invalidEls.get(1).tagName.toLowerCase());
                const invalidCssClass = form.invalidCssClass;
                let i = 0;
                $invalidEls.each(function () {
                    const $el = $(this);
                    check_1.checkEqual(form_1.RequiredElValidator.EmptyValueMessage, $el.next().text());
                    check_1.checkTrue($el.hasClass(invalidCssClass));
                    const $elContainer = $el.closest('.' + form.elContainerCssClass);
                    check_1.checkTrue($elContainer.hasClass(invalidCssClass));
                    check_1.checkTrue($elContainer.hasClass('has-error'));
                    i++;
                });
                check_1.checkEqual(2, i);
                check_1.checkLength(1, $form.find('.has-error'));
                const $button = $form.find('input[type=button]');
                check_1.checkLength(1, $button);
                check_1.checkTrue(form.hasErrors());
                check_1.checkTrue($form.hasClass(invalidCssClass));
                form.removeErrors();
                check_1.checkFalse($form.hasClass(invalidCssClass));
                check_1.checkFalse(form.hasErrors());
                checkNoFormErrors($form);
            });
            it('Hides errors after node change', function () {
                const $form = Page.withRequiredElsFormEl();
                const form = new form_1.Form({ el: $form });
                form.validate();
                const $textarea = $form.find('textarea');
                const errorEl = () => $textarea.next('.error');
                check_1.checkLength(1, errorEl());
                $textarea.trigger('change');
                check_1.checkLength(0, errorEl());
            });
            it('invalidEls() before validation', function () {
                const form = new form_1.Form({ el: Page.withRequiredElsFormEl() });
                check_1.checkLength(0, form.invalidEls());
            });
            it('Form has "novalidate" attribute', function () {
                const $el = Page.emptyFormEl();
                expect($el.attr('novalidate')).toBeUndefined();
                new form_1.Form({ el: $el });
                check_1.checkEqual('novalidate', $el.attr('novalidate'));
            });
        });
        it('Is Widget', function () {
            check_1.checkTrue(Page.emptyForm() instanceof widget_3.Widget);
        });
        it('isRequiredEl()', function () {
            check_1.checkTrue(form_1.Form.isRequiredEl(Page.requireEl()));
            check_1.checkFalse(form_1.Form.isRequiredEl(Page.nonRequiredEl()));
        });
        it('els() non-empty form', function () {
            const form = new form_1.Form({ el: $('#non-empty-form') });
            check_1.checkLength(Page.numberOfElsOfNonEmptyForm, form.els());
        });
        it('els() empty form', function () {
            check_1.checkLength(0, Page.emptyForm().els());
        });
        it('elValue()', function () {
            const $form = $('#with-values-form');
            check_1.checkEqual('bar', form_1.Form.elValue($form.find("[name='textBox']")));
            const $checkbox = $form.find("[name='checkBox']");
            check_1.checkEqual(0, form_1.Form.elValue($checkbox));
            $checkbox.prop('checked', true);
            check_1.checkEqual(1, form_1.Form.elValue($checkbox));
        });
        it('send() - response errors', function (done) {
            const form = new form_1.Form({ el: $('#server-error-form') });
            form.send()
                .then(() => {
                check_1.checkTrue(form.hasErrors());
                done();
            });
        });
        it('send() - success response', function (done) {
            class RedirectForm extends form_1.Form {
                constructor() {
                    super(...arguments);
                    Object.defineProperty(this, "successHandlerArgs", {
                        enumerable: true,
                        configurable: true,
                        writable: true,
                        value: void 0
                    });
                }
                handleOkResponse(responseData) {
                    this.successHandlerArgs = Array.prototype.slice.call(arguments);
                }
            }
            const form = new RedirectForm({ el: $('#redirect-form') });
            form.send()
                .then(() => {
                check_1.checkEqual([{ redirect: "/go/to/linux" }], form.successHandlerArgs);
                done();
            });
        });
        it("Default browser's submit handler is not called", function (done) {
            class TestForm extends form_1.Form {
                constructor() {
                    super(...arguments);
                    Object.defineProperty(this, "ajaxHandlerCalled", {
                        enumerable: true,
                        configurable: true,
                        writable: true,
                        value: void 0
                    });
                }
                ajaxSuccess(responseData, textStatus, jqXHR) {
                    this.ajaxHandlerCalled = true;
                }
                ajaxError(jqXHR, textStatus, errorThrown) {
                    this.ajaxHandlerCalled = true;
                }
            }
            const $form = Page.withRequiredElsFormEl();
            const form = new TestForm({ el: $form });
            form.skipValidation = true;
            $form.trigger('submit');
            const intervalId = setInterval(function () {
                if (form.ajaxHandlerCalled) {
                    clearInterval(intervalId);
                    check_1.checkTrue(true);
                    done();
                }
            }, 200);
        });
        it('hasErrors() initial state', function () {
            check_1.checkFalse(Page.withRequiredElsForm().hasErrors());
        });
        it('hasErrors() after showErrors()', function () {
            const $form = Page.emptyFormEl();
            const form = new form_1.Form({ el: $form });
            const messageText = "This is a test";
            form.showErrors([new message_2.ErrorMessage(messageText)]);
            function formMessageContainerEl() {
                return $form.find('.' + form.formMessageContainerCssClass);
            }
            check_1.checkTrue(form.hasErrors());
            check_1.checkEqual(messageText, formMessageContainerEl().text());
            form.removeErrors();
            check_1.checkNoEl(formMessageContainerEl());
            check_1.checkFalse(form.hasErrors());
        });
    });
});
define("localhost/lib/test/jasmine", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.TestResultsReporter = exports.bootJasmine = void 0;
    class ExceptionFormatter {
        message(error) {
            let message = '';
            if (error.name && error.message) {
                message += error.name + ': ' + error.message;
            }
            else {
                message += error.toString() + ' thrown';
            }
            if (error.fileName || error.sourceURL) {
                message += ' in ' + (error.fileName || error.sourceURL);
            }
            if (error.line || error.lineNumber) {
                message += ' (line ' + (error.line || error.lineNumber) + ')';
            }
            return message;
        }
        stack(error) {
            if (!error) {
                return '';
            }
            return error.stack || '';
        }
    }
    function bootJasmine() {
        jasmineRequire.ExceptionFormatter = () => { return ExceptionFormatter; };
        window.jasmine = jasmineRequire.core(jasmineRequire);
        const env = jasmine.getEnv();
        const jasmineInterface = jasmineRequire.interface(jasmine, env);
        extend(window, jasmineInterface);
        window.setTimeout = window.setTimeout;
        window.setInterval = window.setInterval;
        window.clearTimeout = window.clearTimeout;
        window.clearInterval = window.clearInterval;
        function extend(destination, source) {
            for (let property in source) {
                destination[property] = source[property];
            }
            return destination;
        }
        return env;
    }
    exports.bootJasmine = bootJasmine;
    class TestResultsReporter {
        constructor(container, stackTraceFormatter) {
            Object.defineProperty(this, "el", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "results", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "stackTraceFormatter", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "suites", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: []
            });
            Object.defineProperty(this, "summary", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: {
                    noOfTests: 0,
                    noOfFailedTests: 0
                }
            });
            Object.defineProperty(this, "firstTest", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: false
            });
            this.el = $('<div class="panel panel-default test-results"></div>').prependTo(container);
            this.stackTraceFormatter = stackTraceFormatter;
        }
        jasmineStarted(suiteInfo) {
            this.el.prepend('<div class="panel-heading">Testing results</div>');
            this.el.append('<div class="panel-body"></div>');
            this.append('<div class="test-results__intro">Total tests: ' + this.escape((suiteInfo.totalSpecsDefined || 0) + '') + '</div>');
            this.summary.noOfFailedTests = this.summary.noOfTests = 0;
            this.suites = [];
        }
        jasmineDone(runDetails) {
            const summary = this.summary;
            this.append('All tests completed.<br>Passed: ' + this.escape((summary.noOfTests - summary.noOfFailedTests) + '') + '/' + this.escape(summary.noOfTests + ''));
            this.el.addClass(summary.noOfFailedTests > 0 ? 'test-results__failed' : 'test-results__successful');
        }
        suiteStarted(result) {
            const suiteTitle = result.description;
            this.append('<h5 class="test-results__suite test-results__suite_started">'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Suite \'' + this.escape(suiteTitle) + '\' started...'
                + '</h5>');
            this.suites.push({
                title: suiteTitle,
                noOfTests: 0,
                noOfFailedTests: 0
            });
            this.firstTest = true;
        }
        suiteDone(result) {
            const suite = this.suites.pop();
            this.append('<h5 class="test-results__suite test-results__suite_finished">'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Suite \'' + this.escape(suite.title) + '\' finished'
                + '<br>'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Passed : ' + (suite.noOfTests - suite.noOfFailedTests) + '/' + suite.noOfTests
                + ' (not aggregating results of descendant suites)</h5>');
            if (this.suites.length === 0) {
                this.append('<hr>');
            }
            this.firstTest = true;
        }
        specDone(result) {
            const success = !result.failedExpectations || result.failedExpectations.length === 0;
            let doneHtml = '';
            if (success) {
                doneHtml += this.formatSuccessfulTest(result);
                this.append(doneHtml);
            }
            else {
                doneHtml += this.formatFailedTest(result);
                this.append(doneHtml);
                this.applySourceMaps();
                this.summary.noOfFailedTests++;
            }
            const suite = this.suites[this.suites.length - 1];
            suite.noOfTests++;
            this.summary.noOfTests++;
            if (!success) {
                suite.noOfFailedTests++;
            }
        }
        applySourceMaps() {
            const self = this;
            self.el.find('.test-results__stack-trace:not(.processed)').each(function () {
                const $el = $(this);
                $el.addClass('processed');
                const $stackTrace = $el.find('.test-results__stack');
                self.stackTraceFormatter($stackTrace.text())
                    .then(function (stack) {
                    stack = self.highlightStackTraceLines(stack);
                    $stackTrace.html(stack);
                    $el.find('.test-results__stack-loading-indicator').remove();
                    $stackTrace.show();
                });
            });
        }
        formatSuccessfulTest(result) {
            let indent = '';
            if (this.firstTest) {
                indent = this.indent(this.suites.length);
                this.firstTest = false;
            }
            const testTitle = result.description;
            let doneHtml = indent + '<span title="' + this.escape(testTitle) + '" class="test-results__test';
            doneHtml += ' test-results__successful-test">✓</span>';
            return doneHtml;
        }
        formatFailedTest(result) {
            let indent = '';
            if (this.firstTest) {
                indent = this.indent(this.suites.length);
                this.firstTest = false;
            }
            const testTitle = result.description;
            let doneHtml = indent + '<span title="' + this.escape(testTitle) + '" class="test-results__test';
            doneHtml += ' test-results__failed-test">✕</span> ' + this.escape(testTitle);
            const failedExpectations = result.failedExpectations || [];
            for (let i = 0; i < failedExpectations.length; i++) {
                const expectation = failedExpectations[i];
                doneHtml += '<div class="test-results__failed-test-message">' + this.escape(expectation.message) + '</div>';
                doneHtml += '<pre class="test-results__stack-trace"><div class="test-results__stack-loading-indicator">Loading stack trace, please wait...</div><div class="test-results__stack" style="display: none;">' + this.escape(expectation.stack) + '</div></pre>';
            }
            return doneHtml;
        }
        highlightStackTraceLines(stack) {
            const lines = stack.split("\n");
            const isTsLine = (line) => /\s*at.*?\s+\([^)]+\.ts:\d+:\d+\)$/.test(line);
            return lines.map((line, index) => {
                line = line.trim();
                if (isTsLine(line)) {
                    const lastTsLine = lines[index + 1] === undefined || !isTsLine(lines[index + 1]);
                    return '<div class="test-results__stack-line test-results__stack-line_ts' + (lastTsLine ? ' test-results__stack-line_ts-last' : '') + '">' + this.escape(line) + '</div>';
                }
                return `<div class="test-results__stack-line">${this.escape(line)}</div>`;
            }).join("");
        }
        escape(str) {
            return jasmineRequire.util().htmlEscape(str);
        }
        append(html) {
            this.el.find('.panel-body').append(html);
        }
        dump(obj) {
            return '<pre>' + this.escape(JSON.stringify(obj)) + '</pre>';
        }
        indent(length) {
            let s = '';
            for (let i = 0; i < length; i++) {
                s += '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            return s;
        }
    }
    exports.TestResultsReporter = TestResultsReporter;
});
define("localhost/test/index", ["require", "exports", "localhost/lib/test/jasmine"], function (require, exports, jasmine_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.main = void 0;
    const env = jasmine_1.bootJasmine();
    function main() {
        const container = $('#main__body');
        const stackTraceFormatter = (stack) => {
            return Promise.resolve(stack);
        };
        env.addReporter(new jasmine_1.TestResultsReporter(container, stackTraceFormatter));
        const seleniumReporter = {
            jasmineDone(runDetails) {
                document.getElementById('main__body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
            }
        };
        env.addReporter(seleniumReporter);
        env.execute();
    }
    exports.main = main;
});
define("localhost/test/message-test", ["require", "exports", "localhost/lib/base/message", "localhost/lib/test/check"], function (require, exports, message_3, check_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    describe("Message", function () {
        describe('this is before', function () {
            it('foo', function () {
                check_2.checkTrue(true);
            });
        });
        [message_3.MessageType.Error, message_3.MessageType.Warning, message_3.MessageType.Info, message_3.MessageType.Debug].forEach(function (messageType) {
            it('renderMessage() - all message types', function () {
                const text = '<div>Random {0} warning "!" {1} has been occurred.</div>';
                const args = ['<b>system</b>', '<div>for <b>unknown</b> reason</div>'];
                const message = new message_3.Message(messageType, text, args);
                const cssClass = message_3.MessageType[messageType].toLowerCase();
                check_2.checkEqual('<div class="' + cssClass + '">&lt;div&gt;Random <b>system</b> warning &quot;!&quot; <div>for <b>unknown</b> reason</div> has been occurred.&lt;/div&gt;</div>', message_3.renderMessage(message));
            });
        });
        describe('this is after', function () {
            it('foo', function () {
                check_2.checkTrue(true);
            });
        });
    });
});
define("localhost/lib/base/app", ["require", "exports", "localhost/lib/base/message"], function (require, exports, message_4) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.App = void 0;
    class App {
        constructor() {
            Object.defineProperty(this, "context", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: {}
            });
            this.context.pageMessenger = new message_4.PageMessenger({ el: $('#page-messages') });
            this.bindEventHandlers();
        }
        bindEventHandlers() {
        }
    }
    exports.App = App;
});
define("localhost/lib/base/bom", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    Math.EPS = 0.000001;
    Math.roundFloat = function (val, precision = 2) {
        const dd = Math.pow(10, precision);
        return Math.round(val * dd) / dd;
    };
    Math.floatLessThanZero = function (val) {
        return val < -Math.EPS;
    };
    Math.floatGreaterThanZero = function (val) {
        return val > Math.EPS;
    };
    Math.floatEqualZero = function (val) {
        return Math.abs(val) <= Math.EPS;
    };
    Math.floatsEqual = function (a, b) {
        return Math.floatEqualZero(a - b);
    };
    Math.logN = function (n, base) {
        return Math.log(n) / Math.log(base);
    };
    String.prototype.e = function () {
        const entityMap = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': '&quot;',
            "'": '&#39;'
        };
        return this.replace(/[&<>"']/g, function (s) {
            return entityMap[s];
        });
    };
    String.prototype.titleize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
    String.prototype.format = function (args, filter) {
        let val = this;
        args.forEach((arg, index) => {
            val = val.replace('{' + index + '}', filter ? filter(arg) : arg);
        });
        return val;
    };
    String.prototype.nl2Br = function () {
        return this.replace(/\r?\n/g, '<br>');
    };
    String.prototype.replaceAll = function (search, replace) {
        return this.split(search).join(replace);
    };
    String.prototype.ucFirst = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
    String.prototype.trimR = function (chars) {
        if (chars === undefined) {
            return this.replace(new RegExp('\\s+$'), '');
        }
        return this.replace(new RegExp("[" + RegExp.e(chars) + "]+$"), '');
    };
    String.prototype.trimL = function (chars) {
        if (chars === undefined) {
            return this.replace(new RegExp('^\\s+'), '');
        }
        return this.replace(new RegExp("^[" + RegExp.e(chars) + "]+"), '');
    };
    String.prototype.trimLR = function (chars) {
        if (chars == undefined) {
            return this.trim();
        }
        return this.trimL(chars).trimR(chars);
    };
    RegExp.e = function (s) {
        return String(s).replace(/[\\^$*+?.()|[\]{}]/g, '\\$&');
    };
    Object.pick = function (object, keys) {
        return keys.reduce((obj, key) => {
            if (object && object.hasOwnProperty(key)) {
                obj[key] = object[key];
            }
            return obj;
        }, {});
    };
});
define("localhost/lib/base/error", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UnexpectedValueException = exports.NotImplementedException = exports.Exception = void 0;
    class Exception extends Error {
        constructor(message) {
            super(message);
            Object.defineProperty(this, "message", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: message
            });
            this.name = 'Exception';
            this.message = message;
        }
        toString() {
            return this.name + ': ' + this.message;
        }
    }
    exports.Exception = Exception;
    class NotImplementedException extends Exception {
    }
    exports.NotImplementedException = NotImplementedException;
    class UnexpectedValueException extends Exception {
    }
    exports.UnexpectedValueException = UnexpectedValueException;
});
define("localhost/lib/base/grid", ["require", "exports", "localhost/lib/base/widget"], function (require, exports, widget_4) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Grid = void 0;
    class Grid extends widget_4.Widget {
        normalizeConf(conf) {
            return conf;
        }
        addRow(entity) {
            const tpl = this.rowTpl(entity);
            this.el.find('tr:last').after(tpl.content);
        }
        updateRow(entity) {
            const id = entity.id;
            const tpl = this.rowTpl(entity);
            this.el.find('#entity' + id).replaceWith(tpl.content);
        }
        rowAndEntityId(clickedEl) {
            const $row = $(clickedEl).closest('tr');
            const entityId = $row.attr('id').split('-').pop();
            return [$row, entityId];
        }
        rowTpl(entity) {
            const rowTplClone = $(this.el.attr('id') + '-row-tpl')[0].cloneNode(true);
            console.log(rowTplClone);
            function replacePlaceholders(html, entity, prefix, suffix) {
                for (const [key, val] of Object.entries(entity)) {
                    if (typeof val === 'object') {
                        html = replacePlaceholders(html, val, prefix + key + '[', ']');
                    }
                    else {
                        html = html.replace(prefix + key + suffix, String(val).e());
                    }
                }
                return html;
            }
            rowTplClone.innerHTML = replacePlaceholders(rowTplClone.innerHTML, entity, '$', '');
            return rowTplClone;
        }
    }
    exports.Grid = Grid;
});
class Uri {
}
class Http {
    get(uri) {
    }
    delete(uri) {
    }
    head(uri) {
    }
    options(uri) {
    }
    patch(uri) {
    }
    post(uri) {
    }
    put(uri) {
    }
}
define("localhost/lib/base/i18n", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.tr = void 0;
    function tr(message) {
        return message;
    }
    exports.tr = tr;
});
define("localhost/lib/base/jquery-ext", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.__dummy = void 0;
    (() => {
        let uniqId = 0;
        $.fn.once = function (fn) {
            let cssClass = String(uniqId++) + '-processed';
            return this.not('.' + cssClass)
                .addClass(cssClass)
                .each(fn);
        };
    })();
    $.resolvedPromise = function (value, ...args) {
        return $.Deferred().resolve(value, ...args).promise();
    };
    $.rejectedPromise = function (value, ...args) {
        return $.Deferred().reject(value, ...args).promise();
    };
    exports.__dummy = null;
    $.fn.extend({
        uniqId: (function () {
            var uuid = 0;
            return function () {
                return this.each(function () {
                    if (!this.id) {
                        this.id = "ui-id-" + (++uuid);
                    }
                });
            };
        })(),
        removeUniqId: function () {
            return this.each(function () {
                if (/^ui-id-\d+$/.test(this.id)) {
                    $(this).removeAttr("id");
                }
            });
        }
    });
});
define("localhost/lib/base/keyboard", ["require", "exports", "keymaster"], function (require, exports, bindKey_) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.bindKey = void 0;
    function bindKey(key, handler) {
        bindKey_(key, handler);
    }
    exports.bindKey = bindKey;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJib20tdGVzdC50cyIsIi4uLy4uL2xpYi9iYXNlL2V2ZW50LW1hbmFnZXIudHMiLCIuLi8uLi9saWIvYmFzZS93aWRnZXQudHMiLCIuLi8uLi9saWIvYmFzZS9tZXNzYWdlLnRzIiwiLi4vLi4vbGliL2Jhc2UvYmFzZS50cyIsIi4uLy4uL2xpYi9iYXNlL2Zvcm0udHMiLCIuLi8uLi9saWIvdGVzdC9jaGVjay50cyIsImZvcm0tdGVzdC50cyIsIi4uLy4uL2xpYi90ZXN0L2phc21pbmUudHMiLCJpbmRleC50cyIsIm1lc3NhZ2UtdGVzdC50cyIsIi4uLy4uL2xpYi9iYXNlL2FwcC50cyIsIi4uLy4uL2xpYi9iYXNlL2JvbS50cyIsIi4uLy4uL2xpYi9iYXNlL2Vycm9yLnRzIiwiLi4vLi4vbGliL2Jhc2UvZ3JpZC50cyIsIi4uLy4uL2xpYi9iYXNlL2h0dHAudHMiLCIuLi8uLi9saWIvYmFzZS9pMThuLnRzIiwiLi4vLi4vbGliL2Jhc2UvanF1ZXJ5LWV4dC50cyIsIi4uLy4uL2xpYi9iYXNlL2tleWJvYXJkLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7QUFRQSxRQUFRLENBQUMsNkNBQTZDLEVBQUU7SUFDcEQsUUFBUSxDQUFDLFlBQVksRUFBRTtRQUNuQixFQUFFLENBQUMsaUJBQWlCLEVBQUUsR0FBRyxFQUFFLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsRUFBRSxFQUFFLENBQUMsQ0FBQyxFQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFFLENBQUMsQ0FBQztRQUV0RyxFQUFFLENBQUMsd0JBQXdCLEVBQUUsR0FBRyxFQUFFLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFFLENBQUMsQ0FBQztRQUN6RixFQUFFLENBQUMs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