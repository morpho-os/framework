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
define("localhost/lib/base/http", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.queryArgs = exports.redirectTo = exports.redirectToHome = exports.redirectToSelf = exports.isResponseError = exports.RestAction = void 0;
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
    var RestAction;
    (function (RestAction) {
        RestAction["Delete"] = "delete";
    })(RestAction = exports.RestAction || (exports.RestAction = {}));
    function isResponseError(response) {
        return !response.ok;
    }
    exports.isResponseError = isResponseError;
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
});
define("localhost/lib/base/base", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.delayedCallback = exports.showUnknownError = exports.Re = exports.isGenerator = exports.isDomNode = exports.isPromise = exports.lname = exports.id = void 0;
    function id(value) {
        return value;
    }
    exports.id = id;
    function lname(name) {
        name = name.replace('_', '-');
        name = name.replace(/[a-z][A-Z]/, function camelizeNextCh(match) {
            return match[0] + '-' + match[1].toLowerCase();
        });
        name = name.replace(/[^-A-Za-z.0-9]/, '-');
        name = name.replace(/-+/, '-');
        return name;
    }
    exports.lname = lname;
    function isPromise(val) {
        return val && typeof val.promise === 'function';
    }
    exports.isPromise = isPromise;
    function isDomNode(obj) {
        return obj && obj.nodeType > 0;
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
});
define("localhost/lib/base/widget", ["require", "exports", "localhost/lib/base/event-manager"], function (require, exports, event_manager_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showResponseErr = exports.errorToast = exports.okToast = exports.Widget = void 0;
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
        dispose() {
        }
        init() {
            if (this.conf && this.conf.el) {
                this.el = $(this.conf.el);
            }
        }
        bindHandlers() {
        }
        normalizeConf(conf) {
            if (conf instanceof jQuery) {
                return { el: conf };
            }
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
    function errorToast(text = undefined) {
        Toastify({
            text: text || 'Error.',
            backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
            className: "info",
        }).showToast();
    }
    exports.errorToast = errorToast;
    function showResponseErr(response) {
        if (response.err && typeof response.err == 'string') {
            errorToast(response.err);
        }
        else {
            errorToast();
        }
    }
    exports.showResponseErr = showResponseErr;
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
define("localhost/lib/base/app", ["require", "exports", "localhost/lib/base/message"], function (require, exports, message_1) {
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
            this.context.pageMessenger = new message_1.PageMessenger({ el: $('#page-messages') });
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
define("localhost/lib/base/form", ["require", "exports", "localhost/lib/base/message", "localhost/lib/base/widget", "localhost/lib/base/http"], function (require, exports, message_2, widget_2, http_1) {
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
                    errors.push([$el, elErrors.map((error) => { return new message_2.ErrorMessage(error); })]);
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
                const rendered = '<div class="alert alert-error">' + errors.map(message_2.renderMessage).join("\n") + '</div>';
                this.formMessageContainerEl()
                    .prepend(rendered);
            }
            this.el.addClass(this.invalidCssClass);
        }
        showElErrors($el, errors) {
            const invalidCssClass = this.invalidCssClass;
            $el.addClass(invalidCssClass).closest('.' + this.elContainerCssClass).addClass(invalidCssClass).addClass('has-error');
            $el.after(errors.map(message_2.renderMessage).join("\n"));
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
                http_1.redirectTo(responseData.redirect);
                return true;
            }
        }
        handleErrResponse(responseData) {
            if (Array.isArray(responseData)) {
                const errors = responseData.map((message) => {
                    return new message_2.ErrorMessage(message.text, message.args);
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
define("localhost/lib/base/grid", ["require", "exports", "localhost/lib/base/widget"], function (require, exports, widget_3) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Grid = void 0;
    class Grid extends widget_3.Widget {
        bindHandlers() {
            super.bindHandlers();
            this.el.find('.grid__chk').on('change', function () {
                console.log(this);
            });
        }
        checkAllCheckboxes() {
            this.checkboxes().prop('checked', true).trigger('change');
        }
        uncheckAllCheckboxes() {
            this.checkboxes().prop('checked', false).trigger('change');
        }
        checkedCheckboxes() {
            return this.checkboxes(':checked');
        }
        checkboxes(selector) {
            return this.el.find('.grid__chk' + (selector || ''));
        }
        isActionButtonsDisabled() {
            const actionsButtons = this.actionButtons();
            if (!actionsButtons.length) {
                throw new Error("Empty action buttons");
            }
            return actionsButtons.filter(':not(.disabled)').length === 0;
        }
        actionButtons() {
            return this.el.find('.grid__action-btn');
        }
    }
    exports.Grid = Grid;
});
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
define("localhost/lib/base/keyboard", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.bind = void 0;
    function bind(k, handler) {
    }
    exports.bind = bind;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJldmVudC1tYW5hZ2VyLnRzIiwiaHR0cC50cyIsImJhc2UudHMiLCJ3aWRnZXQudHMiLCJtZXNzYWdlLnRzIiwiYXBwLnRzIiwiYm9tLnRzIiwiZXJyb3IudHMiLCJmb3JtLnRzIiwiZ3JpZC50cyIsImkxOG4udHMiLCJqcXVlcnktZXh0LnRzIiwia2V5Ym9hcmQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7OztJQVNBLE1BQWEsWUFBWTtRQUF6QjtZQUNJOzs7O3VCQUEyRCxFQUFFO2VBQUM7UUFrQmxFLENBQUM7UUFoQlUsRUFBRSxDQUFDLFNBQWlCLEVBQUUsT0FBb0I7WUFDN0MsSUFBSSxDQUFDLFFBQVEsQ0FBQyxTQUFTLENBQUMsR0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUUsQ0FBQztZQUMxRCxJQUFJLENBQUMsUUFBUSxDQUFDLFNBQVMsQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQztRQUMzQyxDQUFDO1FBRU0sT0FBTyxDQUFDLFNBQWlCLEVBQUUsR0FBRyxJQUFXO1lBQzVDLElBQUksUUFBUSxHQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsU0FBUyxDQUFDLENBQUM7WUFDeEMsSUFBSSxDQUFDLFFBQVEsRUFBRTtnQkFDWCxPQUFPO2FBQ1Y7WUFDRCxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsUUFBUSxDQUFDLE1BQU0sRUFBRSxFQUFFLENBQUMsRUFBRTtnQkFDdEMsSUFBSSxLQUFLLEtBQUssUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBSSxDQUFDLEVBQUU7b0JBQ2hDLE1BQU07aUJBQ1Q7YUFDSjtRQUNMLENBQUM7S0FDSjtJQW5CRCxvQ0FtQkM7Ozs7OztJQzVCRCxNQUFNLEdBQUc7S0FFUjtJQUVELE1BQU0sSUFBSTtRQUNDLEdBQUcsQ0FBQyxHQUFpQjtRQUU1QixDQUFDO1FB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