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
            this.handlers = {};
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
            this.type = type;
            this.text = text;
            this.args = args;
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
    Re.email = /^[^@]+@[^@]+$/;
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
    RequiredElValidator.EmptyValueMessage = 'This field is required';
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
    Form.defaultInvalidCssClass = 'invalid';
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
    Page.numberOfElsOfNonEmptyForm = 26;
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
            this.suites = [];
            this.summary = {
                noOfTests: 0,
                noOfFailedTests: 0
            };
            this.firstTest = false;
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
            this.context = {};
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
            this.message = message;
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