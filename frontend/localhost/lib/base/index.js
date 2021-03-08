"use strict";
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
define("localhost/lib/base/app", ["require", "exports", "localhost/lib/base/message"], function (require, exports, message_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.App = void 0;
    class App {
        constructor() {
            this.context = {};
            this.context.pageMessenger = new message_1.PageMessenger({ el: $('#page-messages') });
            this.bindEventHandlers();
        }
        bindEventHandlers() {
        }
    }
    exports.App = App;
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
define("localhost/lib/base/form", ["require", "exports", "localhost/lib/base/message", "localhost/lib/base/widget", "localhost/lib/base/base"], function (require, exports, message_2, widget_2, base_1) {
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
                base_1.redirectTo(responseData.redirect);
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
    Form.defaultInvalidCssClass = 'invalid';
});
define("localhost/lib/base/grid", ["require", "exports", "localhost/lib/base/widget"], function (require, exports, widget_3) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Grid = void 0;
    class Grid extends widget_3.Widget {
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
define("localhost/app/index", ["require", "exports", "localhost/lib/base/app"], function (require, exports, app_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    window.app = new app_1.App();
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJodHRwLnRzIiwiZXZlbnQtbWFuYWdlci50cyIsIndpZGdldC50cyIsIm1lc3NhZ2UudHMiLCJhcHAudHMiLCJiYXNlLnRzIiwiYm9tLnRzIiwiZXJyb3IudHMiLCJmb3JtLnRzIiwiZ3JpZC50cyIsImkxOG4udHMiLCJqcXVlcnktZXh0LnRzIiwia2V5Ym9hcmQudHMiLCIuLi9hcHAvaW5kZXgudHMiLCIuLi90ZXN0L2NoZWNrLnRzIiwiLi4vdGVzdC9qYXNtaW5lLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7O0lDU0EsTUFBYSxZQUFZO1FBQXpCO1lBQ1ksYUFBUSxHQUEyQyxFQUFFLENBQUM7UUFrQmxFLENBQUM7UUFoQlUsRUFBRSxDQUFDLFNBQWlCLEVBQUUsT0FBb0I7WUFDN0MsSUFBSSxDQUFDLFFBQVEsQ0FBQyxTQUFTLENBQUMsR0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUUsQ0FBQztZQUMxRCxJQUFJLENBQUMsUUFBUSxDQUFDLFNBQVMsQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQztRQUMzQyxDQUFDO1FBRU0sT0FBTyxDQUFDLFNBQWlCLEVBQUUsR0FBRyxJQUFXO1lBQzVDLElBQUksUUFBUSxHQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsU0FBUyxDQUFDLENBQUM7WUFDeEMsSUFBSSxDQUFDLFFBQVEsRUFBRTtnQkFDWCxPQUFPO2FBQ1Y7WUFDRCxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsUUFBUSxDQUFDLE1BQU0sRUFBRSxFQUFFLENBQUMsRUFBRTtnQkFDdEMsSUFBSSxLQUFLLEtBQUssUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBSSxDQUFDLEVBQUU7b0JBQ2hDLE1BQU07aUJBQ1Q7YUFDSjtRQUNMLENBQUM7S0FDSjtJQW5CRCxvQ0FtQkM7Ozs7OztJQ2RELE1BQXNCLE1BQThDLFNBQVEsNEJBQVk7UUFLcEYsWUFBbUIsSUFBVztZQUMxQixLQUFLLEVBQUUsQ0FBQztZQUNSLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDLGFBQWEsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUNyQyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUM7WUFDWixJQUFJLENBQUMsWUFBWSxFQUFFLENBQUM7UUFDeEIsQ0FBQztRQUVTLElBQUk7WUFDVixJQUFJLElBQUksQ0FBQyxJQUFJLElBQUksSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLEVBQUU7Z0JBQzNCLElBQUksQ0FBQyxFQUFFLEdBQUcsQ0FBQyxDQUFTLElBQUksQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUM7YUFDckM7UUFDTCxDQUFDO1FBRVMsWUFBWTtRQUN0QixDQUFDO1FBRVMsYUFBYSxDQUFDLElBQVc7WUFDL0IsT0FBTyxJQUFJLENBQUM7UUFDaEIsQ0FBQztLQUNKO0lBeEJELHdCQXdCQztJQWlCRCxTQUFnQixPQUFPLENBQUMsSUFBWTtRQUNoQyxRQUFRLENBQUM7WUFDTCxJQUFJLEVBQUUsSUFBSTtZQUNWLGVBQWUsRUFBRSw2Q0FBNkM7WUFDOUQsU0FBUyxFQUFFLE1BQU07U0FDcEIsQ0FBQyxDQUFDLFNBQVMsRUFBRSxDQUFDO0lBQ25CLENBQUM7SUFORCwwQkFNQztJQUVELFNBQWdCLFVBQVUsQ0FBQyxPQUFzQixJQUFJO1FBQ2pELFFBQVEsQ0FBQztZQUNMLElBQUksRUFBRSxJQUFJLElBQUksT0FBTztZQUNyQixlQUFlLEVBQUUsNkNBQTZDO1lBQzlELFNBQVMsRUFBRSxNQUFNO1NBQ3BCLENBQUMsQ0FBQyxTQUFTLEVBQUUsQ0FBQztJQUNuQixDQUFDO0lBTkQsZ0NBTUM7Ozs7OztJQzNERCxJQUFZLFdBTVg7SUFORCxXQUFZLFdBQVc7UUFDbkIsK0NBQVMsQ0FBQTtRQUNULG1EQUFXLENBQUE7UUFDWCw2Q0FBUSxDQUFBO1FBQ1IsK0NBQVMsQ0FBQTtRQUNULDRDQUFvQyxDQUFBO0lBQ3hDLENBQUMsRUFOVyxXQUFXLEdBQVgsbUJBQVcsS0FBWCxtQkFBVyxRQU10QjtJQWFELE1BQWEsYUFBYyxTQUFRLGVBQU07UUFDM0IsZ0JBQWdCO1lBQ3RCLE9BQU8sSUFBSSxDQUFDLFVBQVUsRUFBRSxDQUFDLE1BQU0sQ0FBQztRQUNwQyxDQUFDO1FBRVMsVUFBVTtZQUNoQixPQUFPLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO1FBQ2xDLENBQUM7UUFFUyxZQUFZO1lBQ2xCLEtBQUssQ0FBQyxZQUFZLEVBQUUsQ0FBQztZQUNyQixJQUFJLENBQUMsMkJBQTJCLEVBQUUsQ0FBQztRQUN2QyxDQUFDO1FBRVMsMkJBQTJCO1lBQ2pDLE1BQU0sSUFBSSxHQUFHLElBQUksQ0FBQztZQUVsQixTQUFTLGNBQWMsQ0FBQyxHQUFXLEVBQUUsUUFBcUM7Z0JBQ3RFLEdBQUcsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLENBQUM7WUFDMUIsQ0FBQztZQUVELFNBQVMseUJBQXlCO2dCQUM5QixjQUFjLENBQUMsSUFBSSxDQUFDLEVBQUUsRUFBRTtvQkFDcEIsSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsTUFBTSxFQUFFLENBQUM7b0JBQ25DLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxFQUFFLENBQUM7Z0JBQ25CLENBQUMsQ0FBQyxDQUFDO1lBQ1AsQ0FBQztZQUVELFNBQVMsb0JBQW9CLENBQUMsUUFBZ0I7Z0JBQzFDLElBQUksSUFBSSxDQUFDLGdCQUFnQixFQUFFLEtBQUssQ0FBQyxFQUFFO29CQUMvQix5QkFBeUIsRUFBRSxDQUFDO2lCQUMvQjtxQkFBTTtvQkFDSCxNQUFNLGlCQUFpQixHQUFHLFFBQVEsQ0FBQyxPQUFPLENBQUMsV0FBVyxDQUFDLENBQUM7b0JBQ3hELElBQUksaUJBQWlCLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7d0JBQy9DLGNBQWMsQ0FBQyxpQkFBaUIsRUFBRTs0QkFDOUIsaUJBQWlCLENBQUMsTUFBTSxFQUFFLENBQUM7d0JBQy9CLENBQUMsQ0FBQyxDQUFDO3FCQUNOO3lCQUFNO3dCQUNILGNBQWMsQ0FBQyxRQUFRLEVBQUU7NEJBQ3JCLFFBQVEsQ0FBQyxNQUFNLEVBQUUsQ0FBQzt3QkFDdEIsQ0FBQyxDQUFDLENBQUM7cUJBQ047aUJBQ0o7WUFDTCxDQUFDO1lBRUQsSUFBSSxDQUFDLEVBQUUsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLGNBQWMsRUFBRTtnQkFDaEMsb0JBQW9CLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1lBQ3BELENBQUMsQ0FBQyxDQUFDO1lBQ0gsVUFBVSxDQUFDO2dCQUNQLHlCQUF5QixFQUFFLENBQUM7WUFDaEMsQ0FBQyxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ2IsQ0FBQztLQUNKO0lBcERELHNDQW9EQztJQUVELFNBQWdCLGFBQWEsQ0FBQyxPQUFnQjtRQUMxQyxJQUFJLElBQUksR0FBRyxPQUFPLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxDQUFDO1FBQ3JDLElBQUksR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNqQyxPQUFPLFdBQVcsQ0FBQyxJQUFJLEVBQUUsZ0JBQWdCLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7SUFDN0QsQ0FBQztJQUpELHNDQUlDO0lBRUQsU0FBUyxXQUFXLENBQUMsSUFBWSxFQUFFLElBQVk7UUFDM0MsT0FBTyxjQUFjLEdBQUcsSUFBSSxDQUFDLFdBQVcsRUFBRSxDQUFDLFVBQVUsRUFBRSxHQUFHLElBQUksR0FBRyxJQUFJLEdBQUcsUUFBUSxDQUFDO0lBQ3JGLENBQUM7SUFFRCxTQUFnQixnQkFBZ0IsQ0FBQyxJQUFpQjtRQWU5QyxPQUFPLFdBQVcsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUM3QixDQUFDO0lBaEJELDRDQWdCQztJQUVELE1BQWEsT0FBTztRQUNoQixZQUFtQixJQUFpQixFQUFTLElBQVksRUFBUyxPQUFpQixFQUFFO1lBQWxFLFNBQUksR0FBSixJQUFJLENBQWE7WUFBUyxTQUFJLEdBQUosSUFBSSxDQUFRO1lBQVMsU0FBSSxHQUFKLElBQUksQ0FBZTtRQUNyRixDQUFDO1FBRU0sT0FBTyxDQUFDLElBQWlCO1lBQzVCLE9BQU8sSUFBSSxDQUFDLElBQUksS0FBSyxJQUFJLENBQUM7UUFDOUIsQ0FBQztLQUNKO0lBUEQsMEJBT0M7SUFFRCxNQUFhLFlBQWEsU0FBUSxPQUFPO1FBQ3JDLFlBQVksSUFBWSxFQUFFLE9BQWlCLEVBQUU7WUFDekMsS0FBSyxDQUFDLFdBQVcsQ0FBQyxLQUFLLEVBQUUsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ3pDLENBQUM7S0FDSjtJQUpELG9DQUlDO0lBRUQsTUFBYSxjQUFlLFNBQVEsT0FBTztRQUN2QyxZQUFZLElBQVksRUFBRSxPQUFpQixFQUFFO1lBQ3pDLEtBQUssQ0FBQyxXQUFXLENBQUMsT0FBTyxFQUFFLElBQUksRUFBRSxJQUFJLENBQUMsQ0FBQztRQUMzQyxDQUFDO0tBQ0o7SUFKRCx3Q0FJQztJQUVELE1BQWEsV0FBWSxTQUFRLE9BQU87UUFDcEMsWUFBWSxJQUFZLEVBQUUsT0FBaUIsRUFBRTtZQUN6QyxLQUFLLENBQUMsV0FBVyxDQUFDLE9BQU8sRUFBRSxJQUFJLEVBQUUsSUFBSSxDQUFDLENBQUM7UUFDM0MsQ0FBQztLQUNKO0lBSkQsa0NBSUM7SUFFRCxNQUFhLFlBQWEsU0FBUSxPQUFPO1FBQ3JDLFlBQVksSUFBWSxFQUFFLE9BQWlCLEVBQUU7WUFDekMsS0FBSyxDQUFDLFdBQVcsQ0FBQyxLQUFLLEVBQUUsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ3pDLENBQUM7S0FDSjtJQUpELG9DQUlDOzs7Ozs7SUN4SUQsTUFBYSxHQUFHO1FBR1o7WUFGTyxZQUFPLEdBQWdCLEVBQUUsQ0FBQztZQUc3QixJQUFJLENBQUMsT0FBTyxDQUFDLGFBQWEsR0FBRyxJQUFJLHVCQUFhLENBQUMsRUFBQyxFQUFFLEVBQUUsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLEVBQUMsQ0FBQyxDQUFDO1lBQzFFLElBQUksQ0FBQyxpQkFBaUIsRUFBRSxDQUFDO1FBQzdCLENBQUM7UUFFUyxpQkFBaUI7UUFDM0IsQ0FBQztLQUNKO0lBVkQsa0JBVUM7Ozs7OztJQ1BELFNBQWdCLEVBQUUsQ0FBQyxLQUFVO1FBQ3pCLE9BQU8sS0FBSyxDQUFDO0lBQ2pCLENBQUM7SUFGRCxnQkFFQztJQUVELFNBQWdCLFNBQVMsQ0FBQyxHQUFRO1FBQzlCLE9BQU8sR0FBRyxJQUFJLE9BQU8sR0FBRyxDQUFDLE9BQU8sS0FBSyxVQUFVLENBQUM7SUFDcEQsQ0FBQztJQUZELDhCQUVDO0lBR0QsU0FBZ0IsU0FBUyxDQUFDLEdBQVE7UUFDOUIsT0FBTyxHQUFHLENBQUMsUUFBUSxHQUFHLENBQUMsQ0FBQztJQUM1QixDQUFDO0lBRkQsOEJBRUM7SUFFRCxTQUFnQixXQUFXLENBQUMsRUFBWTtRQUNwQyxPQUFhLEVBQUUsQ0FBQyxXQUFZLENBQUMsSUFBSSxLQUFLLG1CQUFtQixDQUFDO0lBQzlELENBQUM7SUFGRCxrQ0FFQztJQUVELE1BQWEsRUFBRTs7SUFBZixnQkFFQztJQUQwQixRQUFLLEdBQUcsZUFBZSxDQUFDO0lBTW5ELFNBQWdCLGdCQUFnQixDQUFDLE9BQWdCO1FBRTdDLEtBQUssQ0FBQyx1Q0FBdUMsQ0FBQyxDQUFDO0lBQ25ELENBQUM7SUFIRCw0Q0FHQztJQUVELFNBQWdCLGNBQWM7UUFFMUIsTUFBTSxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUUsQ0FBQztJQUM3QixDQUFDO0lBSEQsd0NBR0M7SUFFRCxTQUFnQixjQUFjO1FBRzFCLFVBQVUsQ0FBQyxHQUFHLENBQUMsQ0FBQztJQUNwQixDQUFDO0lBSkQsd0NBSUM7SUFFRCxTQUFnQixVQUFVLENBQUMsR0FBVyxFQUFFLGtCQUFrQixHQUFHLElBQUk7UUFDN0QsSUFBSSxrQkFBa0IsRUFBRTtZQUNwQixNQUFNLENBQUMsUUFBUSxDQUFDLElBQUksR0FBRyxHQUFHLENBQUM7U0FDOUI7YUFBTTtZQUNILE1BQU0sQ0FBQyxRQUFRLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDO1NBQ2hDO0lBQ0wsQ0FBQztJQU5ELGdDQU1DO0lBR0QsU0FBZ0IsU0FBUztRQUNyQixNQUFNLE1BQU0sR0FBRyxDQUFDLEtBQWEsRUFBVSxFQUFFLENBQUMsa0JBQWtCLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxLQUFLLEVBQUUsR0FBRyxDQUFDLENBQUMsQ0FBQztRQUV4RixNQUFNLE1BQU0sR0FBRyxxQkFBcUIsQ0FBQztRQUNyQyxJQUFJLFNBQVMsR0FBdUIsRUFBRSxFQUNsQyxJQUFJLENBQUM7UUFFVCxPQUFPLElBQUksR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLEVBQUU7WUFDL0MsSUFBSSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUNyQixLQUFLLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBSzVCLElBQUksR0FBRyxJQUFJLFNBQVMsRUFBRTtnQkFDbEIsU0FBUzthQUNaO1lBQ0QsU0FBUyxDQUFDLEdBQUcsQ0FBQyxHQUFHLEtBQUssQ0FBQztTQUMxQjtRQUVELE9BQU8sU0FBUyxDQUFDO0lBQ3JCLENBQUM7SUFyQkQsOEJBcUJDO0lBSUQsU0FBZ0IsZUFBZSxDQUFDLFFBQWtCLEVBQUUsTUFBYztRQUM5RCxJQUFJLEtBQUssR0FBVyxDQUFDLENBQUM7UUFDdEIsT0FBTztZQUNILE1BQU0sSUFBSSxHQUFHLElBQUksQ0FBQztZQUNsQixNQUFNLElBQUksR0FBRyxTQUFTLENBQUM7WUFDdkIsWUFBWSxDQUFDLEtBQUssQ0FBQyxDQUFDO1lBQ3BCLEtBQUssR0FBRyxNQUFNLENBQUMsVUFBVSxDQUFDO2dCQUN0QixRQUFRLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxJQUFJLENBQUMsQ0FBQztZQUMvQixDQUFDLEVBQUUsTUFBTSxDQUFDLENBQUM7UUFDZixDQUFDLENBQUM7SUFDTixDQUFDO0lBVkQsMENBVUM7SUFFRCxTQUFnQixLQUFLLENBQUMsUUFBd0I7UUFDMUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxFQUFFLENBQUM7SUFDeEIsQ0FBQztJQUZELHNCQUVDOzs7OztJQ3ZGRCxJQUFJLENBQUMsR0FBRyxHQUFHLFFBQVEsQ0FBQztJQUVwQixJQUFJLENBQUMsVUFBVSxHQUFHLFVBQVUsR0FBVyxFQUFFLFlBQW9CLENBQUM7UUFDMUQsTUFBTSxFQUFFLEdBQUcsSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLEVBQUUsU0FBUyxDQUFDLENBQUM7UUFDbkMsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDLEdBQUcsR0FBRyxFQUFFLENBQUMsR0FBRyxFQUFFLENBQUM7SUFDckMsQ0FBQyxDQUFDO0lBQ0YsSUFBSSxDQUFDLGlCQUFpQixHQUFHLFVBQVUsR0FBVztRQUMxQyxPQUFPLEdBQUcsR0FBRyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUM7SUFDM0IsQ0FBQyxDQUFDO0lBQ0YsSUFBSSxDQUFDLG9CQUFvQixHQUFHLFVBQVUsR0FBVztRQUM3QyxPQUFPLEdBQUcsR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDO0lBQzFCLENBQUMsQ0FBQztJQUNGLElBQUksQ0FBQyxjQUFjLEdBQUcsVUFBVSxHQUFXO1FBQ3ZDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFHLENBQUMsSUFBSSxJQUFJLENBQUMsR0FBRyxDQUFDO0lBQ3JDLENBQUMsQ0FBQztJQUNGLElBQUksQ0FBQyxXQUFXLEdBQUcsVUFBVSxDQUFTLEVBQUUsQ0FBUztRQUM3QyxPQUFPLElBQUksQ0FBQyxjQUFjLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO0lBQ3RDLENBQUMsQ0FBQztJQUdGLElBQUksQ0FBQyxJQUFJLEdBQUcsVUFBVSxDQUFTLEVBQUUsSUFBWTtRQUN6QyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUN4QyxDQUFDLENBQUM7SUFLRixNQUFNLENBQUMsU0FBUyxDQUFDLENBQUMsR0FBRztRQUNqQixNQUFNLFNBQVMsR0FBRztZQUNkLEdBQUcsRUFBRSxPQUFPO1lBQ1osR0FBRyxFQUFFLE1BQU07WUFDWCxHQUFHLEVBQUUsTUFBTTtZQUVYLEdBQUcsRUFBRSxRQUFRO1lBQ2IsR0FBRyxFQUFFLE9BQU87U0FDZixDQUFDO1FBQ0YsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLFVBQVUsRUFBRSxVQUFVLENBQVM7WUFDL0MsT0FBYSxTQUFVLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDL0IsQ0FBQyxDQUFDLENBQUM7SUFDUCxDQUFDLENBQUM7SUFFRixNQUFNLENBQUMsU0FBUyxDQUFDLFFBQVEsR0FBRztRQUV4QixPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxFQUFFLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUN4RCxDQUFDLENBQUM7SUFFRixNQUFNLENBQUMsU0FBUyxDQUFDLE1BQU0sR0FBRyxVQUF3QixJQUFjLEVBQUUsTUFBOEI7UUFDNUYsSUFBSSxHQUFHLEdBQUcsSUFBSSxDQUFDO1FBQ2YsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQVcsRUFBRSxLQUFhLEVBQUUsRUFBRTtZQUN4QyxHQUFHLEdBQUcsR0FBRyxDQUFDLE9BQU8sQ0FBQyxHQUFHLEdBQUcsS0FBSyxHQUFHLEdBQUcsRUFBRSxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUM7UUFDckUsQ0FBQyxDQUFDLENBQUM7UUFDSCxPQUFPLEdBQUcsQ0FBQztJQUNmLENBQUMsQ0FBQTtJQUVELE1BQU0sQ0FBQyxTQUFTLENBQUMsS0FBSyxHQUFHO1FBQ3JCLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxRQUFRLEVBQUUsTUFBTSxDQUFDLENBQUM7SUFDMUMsQ0FBQyxDQUFDO0lBQ0YsTUFBTSxDQUFDLFNBQVMsQ0FBQyxVQUFVLEdBQUcsVUFBVSxNQUFjLEVBQUUsT0FBZTtRQUNuRSxPQUFPLElBQUksQ0FBQyxLQUFLLENBQUMsTUFBTSxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDO0lBQzVDLENBQUMsQ0FBQztJQUVGLE1BQU0sQ0FBQyxTQUFTLENBQUMsT0FBTyxHQUFHO1FBQ3ZCLE9BQU8sSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUUsR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQ3hELENBQUMsQ0FBQztJQUdGLE1BQU0sQ0FBQyxTQUFTLENBQUMsS0FBSyxHQUFHLFVBQXdCLEtBQWM7UUFDM0QsSUFBSSxLQUFLLEtBQUssU0FBUyxFQUFFO1lBQ3JCLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxJQUFJLE1BQU0sQ0FBQyxPQUFPLENBQUMsRUFBRSxFQUFFLENBQUMsQ0FBQztTQUNoRDtRQUNELE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxJQUFJLE1BQU0sQ0FBQyxHQUFHLEdBQUcsTUFBTSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxLQUFLLENBQUMsRUFBRSxFQUFFLENBQUMsQ0FBQztJQUN2RSxDQUFDLENBQUM7SUFDRixNQUFNLENBQUMsU0FBUyxDQUFDLEtBQUssR0FBRyxVQUF3QixLQUFjO1FBQzNELElBQUksS0FBSyxLQUFLLFNBQVMsRUFBRTtZQUNyQixPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsSUFBSSxNQUFNLENBQUMsT0FBTyxDQUFDLEVBQUUsRUFBRSxDQUFDLENBQUM7U0FDaEQ7UUFDRCxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsSUFBSSxNQUFNLENBQUMsSUFBSSxHQUFHLE1BQU0sQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsSUFBSSxDQUFDLEVBQUUsRUFBRSxDQUFDLENBQUM7SUFDdkUsQ0FBQyxDQUFDO0lBQ0YsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUcsVUFBd0IsS0FBYztRQUM1RCxJQUFJLEtBQUssSUFBSSxTQUFTLEVBQUU7WUFDcEIsT0FBTyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUM7U0FDdEI7UUFDRCxPQUFPLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQyxDQUFDO0lBQzFDLENBQUMsQ0FBQTtJQU1ELE1BQU0sQ0FBQyxDQUFDLEdBQUcsVUFBVSxDQUFTO1FBQzFCLE9BQU8sTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxxQkFBcUIsRUFBRSxNQUFNLENBQUMsQ0FBQztJQUM1RCxDQUFDLENBQUM7SUFjRixNQUFNLENBQUMsSUFBSSxHQUFHLFVBQVUsTUFBVyxFQUFFLElBQWM7UUFDL0MsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBRyxFQUFFLEdBQUcsRUFBRSxFQUFFO1lBQzVCLElBQUksTUFBTSxJQUFJLE1BQU0sQ0FBQyxjQUFjLENBQUMsR0FBRyxDQUFDLEVBQUU7Z0JBQ3RDLEdBQUcsQ0FBQyxHQUFHLENBQUMsR0FBRyxNQUFNLENBQUMsR0FBRyxDQUFDLENBQUM7YUFDMUI7WUFDRCxPQUFPLEdBQUcsQ0FBQztRQUNmLENBQUMsRUFBeUIsRUFBRSxDQUFDLENBQUM7SUFDbEMsQ0FBQyxDQUFBOzs7Ozs7SUNuSEQsTUFBYSxTQUFVLFNBQVEsS0FBSztRQUdoQyxZQUFtQixPQUFlO1lBQzlCLEtBQUssQ0FBQyxPQUFPLENBQUMsQ0FBQztZQURBLFlBQU8sR0FBUCxPQUFPLENBQVE7WUFFOUIsSUFBSSxDQUFDLElBQUksR0FBRyxXQUFXLENBQUM7WUFDeEIsSUFBSSxDQUFDLE9BQU8sR0FBRyxPQUFPLENBQUM7UUFFM0IsQ0FBQztRQUVNLFFBQVE7WUFDWCxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxHQUFHLElBQUksQ0FBQyxPQUFPLENBQUM7UUFDM0MsQ0FBQztLQUNKO0lBYkQsOEJBYUM7SUFFRCxNQUFhLHVCQUF3QixTQUFRLFNBQVM7S0FDckQ7SUFERCwwREFDQztJQUVELE1BQWEsd0JBQXlCLFNBQVEsU0FBUztLQUN0RDtJQURELDREQUNDOzs7Ozs7SUNaRCxNQUFhLG1CQUFtQjtRQUdyQixRQUFRLENBQUMsR0FBVztZQUN2QixJQUFJLElBQUksQ0FBQyxZQUFZLENBQUMsR0FBRyxDQUFDLEVBQUU7Z0JBQ3hCLElBQUksSUFBSSxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsQ0FBQyxJQUFJLEVBQUUsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFO29CQUNyQyxPQUFPLENBQUMsbUJBQW1CLENBQUMsaUJBQWlCLENBQUMsQ0FBQztpQkFDbEQ7YUFDSjtZQUNELE9BQU8sRUFBRSxDQUFDO1FBQ2QsQ0FBQzs7SUFWTCxrREFXQztJQVYwQixxQ0FBaUIsR0FBRyx3QkFBd0IsQ0FBQztJQWdCeEUsU0FBZ0IsaUJBQWlCO1FBQzdCLE9BQU87WUFDSCxJQUFJLG1CQUFtQixFQUFFO1NBQzVCLENBQUM7SUFDTixDQUFDO0lBSkQsOENBSUM7SUFFRCxTQUFnQixVQUFVLENBQUMsR0FBVyxFQUFFLFVBQTBCO1FBQzlELElBQUksQ0FBQyxVQUFVLEVBQUU7WUFDYixVQUFVLEdBQUcsaUJBQWlCLEVBQUUsQ0FBQztTQUNwQztRQUNELElBQUksTUFBTSxHQUFhLEVBQUUsQ0FBQztRQUMxQixVQUFVLENBQUMsT0FBTyxDQUFDLFVBQVUsU0FBc0I7WUFDL0MsTUFBTSxHQUFHLE1BQU0sQ0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO1FBQ3BELENBQUMsQ0FBQyxDQUFDO1FBQ0gsT0FBTyxNQUFNLENBQUM7SUFDbEIsQ0FBQztJQVRELGdDQVNDO0lBRUQsU0FBZ0IsUUFBUSxDQUFDLEtBQWE7UUFFbEMsTUFBTSxJQUFJLEdBQWtDLEVBQUUsQ0FBQztRQUMvQyxHQUFHLENBQUMsS0FBSyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsS0FBSyxFQUFFLElBQUksRUFBRSxFQUFFO1lBQzVCLE1BQU0sSUFBSSxHQUFHLElBQUksQ0FBQyxZQUFZLENBQUMsTUFBTSxDQUFDLENBQUM7WUFDdkMsSUFBSSxDQUFDLElBQUksRUFBRTtnQkFDUCxPQUFPO2FBQ1Y7WUFDRCxJQUFJLENBQUMsSUFBSSxDQUFDO2dCQUNOLElBQUk7Z0JBQ0osS0FBSyxFQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO2FBQy9CLENBQUMsQ0FBQztRQUNQLENBQUMsQ0FBQyxDQUFDO1FBQ0gsT0FBTyxJQUFJLENBQUM7SUFDaEIsQ0FBQztJQWRELDRCQWNDO0lBRUQsU0FBZ0IsU0FBUyxDQUFDLEtBQWEsRUFBRSxFQUFpRDtRQUN0RixPQUFPLEdBQUcsQ0FBQyxLQUFLLENBQUMsQ0FBQyxJQUFJLENBQUMsVUFBVSxLQUFhLEVBQUUsRUFBZTtZQUMzRCxJQUFJLEtBQUssS0FBSyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFFLEtBQUssQ0FBQyxFQUFFO2dCQUM1QixPQUFPLEtBQUssQ0FBQzthQUNoQjtZQUNELE9BQU8sU0FBUyxDQUFDO1FBQ3JCLENBQUMsQ0FBQyxDQUFDO0lBQ1AsQ0FBQztJQVBELDhCQU9DO0lBRUQsU0FBZ0IsR0FBRyxDQUFDLEtBQWE7UUFDN0IsT0FBTyxDQUFDLENBQVEsS0FBSyxDQUFDLENBQUMsQ0FBRSxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQ3hDLENBQUM7SUFGRCxrQkFFQztJQUVELElBQVksU0FhWDtJQWJELFdBQVksU0FBUztRQUNqQiw4QkFBaUIsQ0FBQTtRQUNqQixrQ0FBcUIsQ0FBQTtRQUNyQiwwQkFBYSxDQUFBO1FBQ2IsOEJBQWlCLENBQUE7UUFDakIsNEJBQWUsQ0FBQTtRQUNmLGtDQUFxQixDQUFBO1FBQ3JCLDRCQUFlLENBQUE7UUFDZiw0QkFBZSxDQUFBO1FBQ2YsOEJBQWlCLENBQUE7UUFDakIsOEJBQWlCLENBQUE7UUFDakIsa0NBQXFCLENBQUE7UUFDckIsK0JBQWtCLENBQUE7SUFDdEIsQ0FBQyxFQWJXLFNBQVMsR0FBVCxpQkFBUyxLQUFULGlCQUFTLFFBYXBCO0lBRVksUUFBQSxjQUFjLEdBQUcsNkJBQTZCLENBQUM7SUFFNUQsTUFBYSxJQUE0QixTQUFRLGVBQWdCO1FBUXRELE1BQU0sQ0FBQyxPQUFPLENBQUMsR0FBVztZQUM3QixJQUFVLEdBQUcsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFFLENBQUMsTUFBTSxDQUFDLEtBQUssVUFBVSxFQUFFO2dCQUMxQyxPQUFPLEdBQUcsQ0FBQyxFQUFFLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2FBQ3JDO1lBQ0QsT0FBTyxHQUFHLENBQUMsR0FBRyxFQUFFLENBQUM7UUFDckIsQ0FBQztRQUVNLE1BQU0sQ0FBQyxZQUFZLENBQUMsR0FBVztZQUNsQyxPQUFPLEdBQUcsQ0FBQyxFQUFFLENBQUMsWUFBWSxDQUFDLENBQUM7UUFDaEMsQ0FBQztRQUVNLEdBQUc7WUFDTixPQUFPLEdBQUcsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUM7UUFDeEIsQ0FBQztRQUVNLGFBQWE7WUFDaEIsT0FBTyxJQUFJLENBQUMsR0FBRyxFQUFFLENBQUMsTUFBTSxDQUFDO2dCQUNyQixNQUFNLEdBQUcsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7Z0JBQ3BCLE9BQU8sR0FBRyxDQUFDLEVBQUUsQ0FBQyxlQUFlLENBQUMsQ0FBQztZQUNuQyxDQUFDLENBQUMsQ0FBQztRQUNQLENBQUM7UUFFTSxRQUFRO1lBQ1gsSUFBSSxDQUFDLFlBQVksRUFBRSxDQUFDO1lBQ3BCLElBQUksTUFBTSxHQUFvQyxFQUFFLENBQUM7WUFDakQsSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDLElBQUksQ0FBQztnQkFDdEIsTUFBTSxHQUFHLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO2dCQUNwQixNQUFNLFFBQVEsR0FBRyxVQUFVLENBQUMsR0FBRyxDQUFDLENBQUM7Z0JBQ2pDLElBQUksUUFBUSxDQUFDLE1BQU0sRUFBRTtvQkFDakIsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUcsRUFBRSxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBYSxFQUFFLEVBQUUsR0FBRyxPQUFPLElBQUksc0JBQVksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztpQkFDNUY7WUFDTCxDQUFDLENBQUMsQ0FBQztZQUNILElBQUksTUFBTSxDQUFDLE1BQU0sRUFBRTtnQkFDZixJQUFJLENBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxDQUFDO2dCQUN4QixPQUFPLEtBQUssQ0FBQzthQUNoQjtZQUNELE9BQU8sSUFBSSxDQUFDO1FBQ2hCLENBQUM7UUFFTSxVQUFVO1lBQ2IsTUFBTSxJQUFJLEdBQUcsSUFBSSxDQUFDO1lBQ2xCLE9BQU8sSUFBSSxDQUFDLEdBQUcsRUFBRSxDQUFDLE1BQU0sQ0FBQztnQkFDckIsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsQ0FBQztZQUNsRCxDQUFDLENBQUMsQ0FBQztRQUNQLENBQUM7UUFFTSxTQUFTO1lBQ1osT0FBTyxJQUFJLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUM7UUFDbEQsQ0FBQztRQU1NLFlBQVk7WUFDZixJQUFJLENBQUMsVUFBVSxFQUFFLENBQUMsSUFBSSxDQUFDLENBQUMsS0FBYSxFQUFFLEVBQWUsRUFBRSxFQUFFO2dCQUN0RCxJQUFJLENBQUMsY0FBYyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDO1lBQy9CLENBQUMsQ0FBQyxDQUFDO1lBQ0gsSUFBSSxDQUFDLHNCQUFzQixFQUFFLENBQUMsTUFBTSxFQUFFLENBQUM7WUFDdkMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDO1FBQzlDLENBQUM7UUFFTSxNQUFNO1lBQ1QsSUFBSSxDQUFDLFlBQVksRUFBRSxDQUFDO1lBQ3BCLElBQUksSUFBSSxDQUFDLGNBQWMsRUFBRTtnQkFDckIsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDO2FBQ2Y7aUJBQU0sSUFBSSxJQUFJLENBQUMsUUFBUSxFQUFFLEVBQUU7Z0JBQ3hCLElBQUksQ0FBQyxJQUFJLEVBQUUsQ0FBQzthQUNmO1FBQ0wsQ0FBQztRQUVNLElBQUk7WUFDUCxJQUFJLENBQUMsc0JBQXNCLEVBQUUsQ0FBQztZQUM5QixPQUFPLElBQUksQ0FBQyxZQUFZLENBQUMsSUFBSSxDQUFDLEdBQUcsRUFBRSxFQUFFLElBQUksQ0FBQyxRQUFRLEVBQUUsQ0FBQyxDQUFDO1FBQzFELENBQUM7UUFLTSxVQUFVLENBQUMsTUFBc0Q7WUFDcEUsSUFBSSxVQUFVLEdBQW1CLEVBQUUsQ0FBQztZQUNwQyxNQUFNLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBNEMsRUFBRSxFQUFFO2dCQUM1RCxJQUFJLEtBQUssQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUU7b0JBQ3BCLE1BQU0sQ0FBQyxHQUFHLEVBQUUsUUFBUSxDQUFDLEdBQUcsR0FBRyxDQUFDO29CQUM1QixJQUFJLENBQUMsWUFBWSxDQUFDLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQztpQkFDcEM7cUJBQU07b0JBQ0gsVUFBVSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztpQkFDeEI7WUFDTCxDQUFDLENBQUMsQ0FBQztZQUNILElBQUksQ0FBQyxjQUFjLENBQUMsVUFBVSxDQUFDLENBQUM7WUFDaEMsSUFBSSxDQUFDLGtCQUFrQixFQUFFLENBQUM7UUFDOUIsQ0FBQztRQUVNLE1BQU0sQ0FBQyxTQUFTLENBQUMsTUFBYztZQUNsQyxNQUFNLFFBQVEsR0FBRyxHQUFHLEVBQUU7Z0JBQ2xCLE1BQU0sUUFBUSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUM7Z0JBQ3JDLE9BQU8sUUFBUSxLQUFLLFNBQVMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsV0FBVyxFQUFFLENBQUM7WUFDaEUsQ0FBQyxDQUFDO1lBQ0YsSUFBSSxhQUFhLENBQUM7WUFDbEIsUUFBUSxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxFQUFFO2dCQUN2QixLQUFLLE9BQU87b0JBQ1IsYUFBYSxHQUFHLFFBQVEsRUFBRSxDQUFDO29CQUMzQixRQUFRLGFBQWEsRUFBRTt3QkFDbkIsS0FBSyxNQUFNOzRCQUNQLE9BQU8sU0FBUyxDQUFDLFNBQVMsQ0FBQzt3QkFDL0IsS0FBSyxPQUFPOzRCQUNSLE9BQU8sU0FBUyxDQUFDLEtBQUssQ0FBQzt3QkFDM0IsS0FBSyxRQUFROzRCQUNULE9BQU8sU0FBUyxDQUFDLE1BQU0sQ0FBQzt3QkFDNUIsS0FBSyxRQUFROzRCQUNULE9BQU8sU0FBUyxDQUFDLE1BQU0sQ0FBQzt3QkFDNUIsS0FBSyxVQUFVOzRCQUNYLE9BQU8sU0FBUyxDQUFDLFFBQVEsQ0FBQzt3QkFDOUIsS0FBSyxNQUFNOzRCQUNQLE9BQU8sU0FBUyxDQUFDLElBQUksQ0FBQzt3QkFDMUIsS0FBSyxRQUFROzRCQUNULE9BQU8sU0FBUyxDQUFDLE1BQU0sQ0FBQzt3QkFDNUIsS0FBSyxPQUFPOzRCQUNSLE9BQU8sU0FBUyxDQUFDLEtBQUssQ0FBQzt3QkFDM0IsS0FBSyxVQUFVOzRCQUNYLE9BQU8sU0FBUyxDQUFDLFFBQVEsQ0FBQzt3QkFDOUIsS0FBSyxPQUFPOzRCQUNSLE9BQU8sU0FBUyxDQUFDLEtBQUssQ0FBQztxQkFDOUI7b0JBQ0QsTUFBTTtnQkFDVixLQUFLLFVBQVU7b0JBQ1gsT0FBTyxTQUFTLENBQUMsUUFBUSxDQUFDO2dCQUM5QixLQUFLLFFBQVE7b0JBQ1QsT0FBTyxTQUFTLENBQUMsTUFBTSxDQUFDO2dCQUM1QixLQUFLLFFBQVE7b0JBQ1QsYUFBYSxHQUFHLFFBQVEsRUFBRSxDQUFDO29CQUMzQixJQUFJLGFBQWEsS0FBSyxFQUFFLElBQUksYUFBYSxLQUFLLFFBQVEsRUFBRTt3QkFDcEQsT0FBTyxTQUFTLENBQUMsTUFBTSxDQUFDO3FCQUMzQjtvQkFDRCxJQUFJLGFBQWEsS0FBSyxRQUFRLEVBQUU7d0JBQzVCLE9BQU8sU0FBUyxDQUFDLE1BQU0sQ0FBQztxQkFDM0I7b0JBQ0QsTUFBTTthQUNiO1lBQ0QsTUFBTSxJQUFJLEtBQUssQ0FBQyxvQkFBb0IsQ0FBQyxDQUFDO1FBQzFDLENBQUM7UUFFUyxjQUFjLENBQUMsTUFBc0I7WUFDM0MsSUFBSSxNQUFNLENBQUMsTUFBTSxFQUFFO2dCQUNmLE1BQU0sUUFBUSxHQUFXLGlDQUFpQyxHQUFHLE1BQU0sQ0FBQyxHQUFHLENBQUMsdUJBQWEsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxRQUFRLENBQUM7Z0JBQzdHLElBQUksQ0FBQyxzQkFBc0IsRUFBRTtxQkFDeEIsT0FBTyxDQUFDLFFBQVEsQ0FBQyxDQUFDO2FBQzFCO1lBQ0QsSUFBSSxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDO1FBQzNDLENBQUM7UUFFUyxZQUFZLENBQUMsR0FBVyxFQUFFLE1BQXNCO1lBQ3RELE1BQU0sZUFBZSxHQUFHLElBQUksQ0FBQyxlQUFlLENBQUM7WUFDN0MsR0FBRyxDQUFDLFFBQVEsQ0FBQyxlQUFlLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxHQUFHLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxlQUFlLENBQUMsQ0FBQyxRQUFRLENBQUMsV0FBVyxDQUFDLENBQUM7WUFDdEgsR0FBRyxDQUFDLEtBQUssQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLHVCQUFhLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztRQUNwRCxDQUFDO1FBRVMsY0FBYyxDQUFDLEdBQVc7WUFDaEMsTUFBTSxVQUFVLEdBQUcsR0FBRyxDQUFDLFdBQVcsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsR0FBRyxJQUFJLENBQUMsbUJBQW1CLENBQUMsQ0FBQztZQUNqRyxJQUFJLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUcsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLE1BQU0sRUFBRTtnQkFDckQsVUFBVSxDQUFDLFdBQVcsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUMsV0FBVyxDQUFDLFdBQVcsQ0FBQyxDQUFDO2FBQ3pFO1lBQ0QsR0FBRyxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxNQUFNLEVBQUUsQ0FBQztRQUNoQyxDQUFDO1FBRVMsc0JBQXNCO1lBQzVCLE1BQU0saUJBQWlCLEdBQUcsSUFBSSxDQUFDLDRCQUE0QixDQUFDO1lBQzVELElBQUksWUFBWSxHQUFHLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBRyxpQkFBaUIsQ0FBQyxDQUFDO1lBQ3pELElBQUksQ0FBQyxZQUFZLENBQUMsTUFBTSxFQUFFO2dCQUN0QixZQUFZLEdBQUcsQ0FBQyxDQUFDLGNBQWMsR0FBRyxpQkFBaUIsR0FBRyxVQUFVLENBQUMsQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDO2FBQ3hGO1lBQ0QsT0FBTyxZQUFZLENBQUM7UUFDeEIsQ0FBQztRQUVTLElBQUk7WUFDVixLQUFLLENBQUMsSUFBSSxFQUFFLENBQUM7WUFDYixJQUFJLENBQUMsY0FBYyxHQUFHLEtBQUssQ0FBQztZQUM1QixJQUFJLENBQUMsbUJBQW1CLEdBQUcsWUFBWSxDQUFDO1lBQ3hDLElBQUksQ0FBQyw0QkFBNEIsR0FBRyxVQUFVLENBQUM7WUFDL0MsSUFBSSxDQUFDLGVBQWUsR0FBRyxJQUFJLENBQUMsc0JBQXNCLENBQUM7WUFDbkQsSUFBSSxDQUFDLGNBQWMsR0FBRyxzQkFBYyxDQUFDO1lBQ3JDLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLFlBQVksRUFBRSxZQUFZLENBQUMsQ0FBQztRQUM3QyxDQUFDO1FBRVMsWUFBWTtZQUNsQixJQUFJLENBQUMsRUFBRSxDQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUUsR0FBRyxFQUFFO2dCQUN0QixJQUFJLENBQUMsTUFBTSxFQUFFLENBQUM7Z0JBQ2QsT0FBTyxLQUFLLENBQUM7WUFDakIsQ0FBQyxDQUFDLENBQUM7WUFDSCxNQUFNLElBQUksR0FBRyxJQUFJLENBQUM7WUFDbEIsSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsY0FBYyxFQUFFO2dCQUN6QyxNQUFNLEdBQUcsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7Z0JBQ3BCLElBQUksR0FBRyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLEVBQUU7b0JBQ3BDLElBQUksQ0FBQyxjQUFjLENBQUMsR0FBRyxDQUFDLENBQUM7aUJBQzVCO1lBQ0wsQ0FBQyxDQUFDLENBQUM7UUFDUCxDQUFDO1FBRVMsWUFBWSxDQUFDLEdBQVcsRUFBRSxXQUFnQjtZQUNoRCxNQUFNLFlBQVksR0FBRyxJQUFJLENBQUMsWUFBWSxFQUFFLENBQUM7WUFDekMsWUFBWSxDQUFDLEdBQUcsR0FBRyxHQUFHLENBQUM7WUFDdkIsWUFBWSxDQUFDLElBQUksR0FBRyxXQUFXLENBQUM7WUFDaEMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDO1FBQ2hDLENBQUM7UUFFUyxZQUFZO1lBQ2xCLE1BQU0sSUFBSSxHQUFHLElBQUksQ0FBQztZQUNsQixPQUFPO2dCQUNILFVBQVUsQ0FBQyxLQUFnQixFQUFFLFFBQTRCO29CQUNyRCxPQUFPLElBQUksQ0FBQyxVQUFVLENBQUMsS0FBSyxFQUFFLFFBQVEsQ0FBQyxDQUFDO2dCQUM1QyxDQUFDO2dCQUNELE9BQU8sQ0FBQyxJQUFTLEVBQUUsVUFBa0IsRUFBRSxLQUFnQjtvQkFDbkQsT0FBTyxJQUFJLENBQUMsV0FBVyxDQUFDLElBQUksRUFBRSxVQUFVLEVBQUUsS0FBSyxDQUFDLENBQUM7Z0JBQ3JELENBQUM7Z0JBQ0QsS0FBSyxDQUFDLEtBQWdCLEVBQUUsVUFBa0IsRUFBRSxXQUFtQjtvQkFDM0QsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUssRUFBRSxVQUFVLEVBQUUsV0FBVyxDQUFDLENBQUM7Z0JBQzFELENBQUM7Z0JBQ0QsTUFBTSxFQUFFLElBQUksQ0FBQyxZQUFZLEVBQUU7YUFDOUIsQ0FBQztRQUNOLENBQUM7UUFFUyxZQUFZO1lBQ2xCLE9BQU8sSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksS0FBSyxDQUFDO1FBQzNDLENBQUM7UUFFUyxVQUFVLENBQUMsS0FBZ0IsRUFBRSxRQUE0QjtRQUNuRSxDQUFDO1FBRVMsV0FBVyxDQUFDLFlBQWlCLEVBQUUsVUFBa0IsRUFBRSxLQUFnQjtZQUN6RSxJQUFJLENBQUMscUJBQXFCLEVBQUUsQ0FBQztZQUM3QixJQUFJLENBQUMsY0FBYyxDQUFDLFlBQVksQ0FBQyxDQUFDO1FBQ3RDLENBQUM7UUFFUyxTQUFTLENBQUMsS0FBZ0IsRUFBRSxVQUFrQixFQUFFLFdBQW1CO1lBQ3pFLElBQUksQ0FBQyxxQkFBcUIsRUFBRSxDQUFDO1lBRTdCLEtBQUssQ0FBQyxZQUFZLENBQUMsQ0FBQztRQUN4QixDQUFDO1FBRVMsUUFBUTtZQUNkLE9BQU8sUUFBUSxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQztRQUM3QixDQUFDO1FBRVMsR0FBRztZQUNULE9BQU8sSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQVUsTUFBTyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUM7UUFDakUsQ0FBQztRQUVTLHFCQUFxQjtZQUMzQixJQUFJLENBQUMsZUFBZSxFQUFFLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxLQUFLLENBQUMsQ0FBQztRQUNuRCxDQUFDO1FBRVMsc0JBQXNCO1lBQzVCLElBQUksQ0FBQyxlQUFlLEVBQUUsQ0FBQyxJQUFJLENBQUMsVUFBVSxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ2xELENBQUM7UUFFUyxlQUFlO1lBQ3JCLE9BQU8sSUFBSSxDQUFDLEdBQUcsRUFBRSxDQUFDLE1BQU0sQ0FBQztnQkFDckIsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBRSxDQUFDLFNBQVMsQ0FBQyxDQUFDO1lBQ2pDLENBQUMsQ0FBQyxDQUFDO1FBQ1AsQ0FBQztRQUVTLGNBQWMsQ0FBQyxNQUFrQjtZQUN2QyxJQUFJLE1BQU0sQ0FBQyxHQUFHLEtBQUssU0FBUyxFQUFFO2dCQUMxQixJQUFJLENBQUMsaUJBQWlCLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQyxDQUFDO2FBQ3RDO2lCQUFNLElBQUksTUFBTSxDQUFDLEVBQUUsS0FBSyxTQUFTLEVBQUU7Z0JBQ2hDLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUM7YUFDcEM7aUJBQU07Z0JBQ0gsSUFBSSxDQUFDLG9CQUFvQixFQUFFLENBQUM7YUFDL0I7UUFDTCxDQUFDO1FBRVMsZ0JBQWdCLENBQUMsWUFBaUI7WUFDeEMsSUFBSSxZQUFZLElBQUksWUFBWSxDQUFDLFFBQVEsRUFBRTtnQkFDdkMsaUJBQVUsQ0FBQyxZQUFZLENBQUMsUUFBUSxDQUFDLENBQUM7Z0JBQ2xDLE9BQU8sSUFBSSxDQUFDO2FBQ2Y7UUFDTCxDQUFDO1FBRVMsaUJBQWlCLENBQUMsWUFBMkI7WUFDbkQsSUFBSSxLQUFLLENBQUMsT0FBTyxDQUFDLFlBQVksQ0FBQyxFQUFFO2dCQUM3QixNQUFNLE1BQU0sR0FBRyxZQUFZLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBNkIsRUFBRSxFQUFFO29CQUM5RCxPQUFPLElBQUksc0JBQVksQ0FBQyxPQUFPLENBQUMsSUFBSSxFQUFFLE9BQU8sQ0FBQyxJQUFJLENBQUMsQ0FBQztnQkFDeEQsQ0FBQyxDQUFDLENBQUM7Z0JBQ0gsSUFBSSxDQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsQ0FBQzthQUMzQjtpQkFBTTtnQkFDSCxJQUFJLENBQUMsb0JBQW9CLEVBQUUsQ0FBQzthQUMvQjtRQUNMLENBQUM7UUFFUyxvQkFBb0I7WUFDMUIsS0FBSyxDQUFDLGtCQUFrQixDQUFDLENBQUM7UUFDOUIsQ0FBQztRQUVTLGtCQUFrQjtZQUN4QixJQUFJLE1BQU0sR0FBRyxJQUFJLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxjQUFjLENBQUMsQ0FBQztZQUMxQyxJQUFJLFVBQVUsR0FBRyxNQUFNLENBQUMsT0FBTyxDQUFDLEdBQUcsR0FBRyxJQUFJLENBQUMsbUJBQW1CLENBQUMsQ0FBQztZQUNoRSxJQUFJLFVBQVUsQ0FBQyxNQUFNLEVBQUU7Z0JBQ25CLE1BQU0sR0FBRyxVQUFVLENBQUM7YUFDdkI7aUJBQU07Z0JBQ0gsVUFBVSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsR0FBRyxHQUFHLElBQUksQ0FBQyw0QkFBNEIsQ0FBQyxDQUFDO2dCQUNyRSxJQUFJLFVBQVUsQ0FBQyxNQUFNLEVBQUU7b0JBQ25CLE1BQU0sR0FBRyxVQUFVLENBQUM7aUJBQ3ZCO2FBQ0o7WUFDRCxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBRTtnQkFDaEIsT0FBTzthQUNWO1FBRUwsQ0FBQzs7SUE1VEwsb0JBNlRDO0lBNVQwQiwyQkFBc0IsR0FBVyxTQUFTLENBQUM7Ozs7OztJQ3pGdEUsTUFBc0IsSUFBSyxTQUFRLGVBQU07UUFDM0IsYUFBYSxDQUFDLElBQVE7WUFDNUIsT0FBTyxJQUFJLENBQUM7UUFDaEIsQ0FBQztRQVNNLE1BQU0sQ0FBVSxNQUFlO1lBQ2xDLE1BQU0sR0FBRyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUM7WUFDaEMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxPQUFPLENBQUMsQ0FBQztRQUMvQyxDQUFDO1FBRU0sU0FBUyxDQUFVLE1BQWdDO1lBQ3RELE1BQU0sRUFBRSxHQUFHLE1BQU0sQ0FBQyxFQUFFLENBQUM7WUFDckIsTUFBTSxHQUFHLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQztZQUNoQyxJQUFJLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUcsRUFBRSxDQUFDLENBQUMsV0FBVyxDQUFDLEdBQUcsQ0FBQyxPQUFPLENBQUMsQ0FBQztRQUMxRCxDQUFDO1FBRVMsY0FBYyxDQUFDLFNBQXNCO1lBQzNDLE1BQU0sSUFBSSxHQUFHLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUM7WUFDeEMsTUFBTSxRQUFRLEdBQXNCLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFFLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEdBQUcsRUFBRSxDQUFDO1lBQ3RFLE9BQU8sQ0FBQyxJQUFJLEVBQUUsUUFBUSxDQUFDLENBQUM7UUFDNUIsQ0FBQztRQUVTLE1BQU0sQ0FBVSxNQUFzQztZQUM1RCxNQUFNLFdBQVcsR0FBd0IsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUUvRixPQUFPLENBQUMsR0FBRyxDQUFDLFdBQVcsQ0FBQyxDQUFBO1lBV3hCLFNBQVMsbUJBQW1CLENBQUMsSUFBWSxFQUFFLE1BQVcsRUFBRSxNQUFjLEVBQUUsTUFBYztnQkFDbEYsS0FBSyxNQUFNLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxJQUFJLE1BQU0sQ0FBQyxPQUFPLENBQUMsTUFBTSxDQUFDLEVBQUU7b0JBQzdDLElBQUksT0FBTyxHQUFHLEtBQUssUUFBUSxFQUFFO3dCQUV6QixJQUFJLEdBQUcsbUJBQW1CLENBQUMsSUFBSSxFQUFFLEdBQUcsRUFBRSxNQUFNLEdBQUcsR0FBRyxHQUFHLEdBQUcsRUFBRSxHQUFHLENBQUMsQ0FBQTtxQkFDakU7eUJBQU07d0JBQ0gsSUFBSSxHQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsTUFBTSxHQUFHLEdBQUcsR0FBRyxNQUFNLEVBQUUsTUFBTSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7cUJBQy9EO2lCQUNKO2dCQUNELE9BQU8sSUFBSSxDQUFDO1lBQ2hCLENBQUM7WUFDRCxXQUFXLENBQUMsU0FBUyxHQUFHLG1CQUFtQixDQUFDLFdBQVcsQ0FBQyxTQUFTLEVBQUUsTUFBTSxFQUFFLEdBQUcsRUFBRSxFQUFFLENBQUMsQ0FBQztZQUNwRixPQUFPLFdBQVcsQ0FBQztRQUN2QixDQUFDO0tBQ0o7SUF6REQsb0JBeURDOztBVC9ERCxNQUFNLEdBQUc7Q0FFUjtBQUVELE1BQU0sSUFBSTtJQUNDLEdBQUcsQ0FBQyxHQUFpQjtJQUU1QixDQUFDO0lBRU0sTUFBTSxDQUFDLEdBQWlCO0lBRS9CLENBQUM7SUFFTSxJQUFJLENBQUMsR0FBaUI7SUFFN0IsQ0FBQztJQUVNLE9BQU8sQ0FBQyxHQUFpQjtJQUVoQyxDQUFDO0lBRU0sS0FBSyxDQUFDLEdBQWlCO0lBRTlCLENBQUM7SUFFTSxJQUFJLENBQUMsR0FBaUI7SUFFN0IsQ0FBQztJQUVNLEdBQUcsQ0FBQyxHQUFpQjtJQUU1QixDQUFDO0NBQ0o7Ozs7O0lVekJELFNBQWdCLEVBQUUsQ0FBQyxPQUFlO1FBRTlCLE9BQU8sT0FBTyxDQUFDO0lBQ25CLENBQUM7SUFIRCxnQkFHQzs7Ozs7O0lDSEQsQ0FBQyxHQUFHLEVBQUU7UUFDRixJQUFJLE1BQU0sR0FBVyxDQUFDLENBQUM7UUFDdkIsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLEdBQUcsVUFBd0IsRUFBaUM7WUFDakUsSUFBSSxRQUFRLEdBQVcsTUFBTSxDQUFDLE1BQU0sRUFBRSxDQUFDLEdBQUcsWUFBWSxDQUFDO1lBQ3ZELE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFHLEdBQUcsUUFBUSxDQUFDO2lCQUMxQixRQUFRLENBQUMsUUFBUSxDQUFDO2lCQUNsQixJQUFJLENBQUMsRUFBRSxDQUFDLENBQUM7UUFDbEIsQ0FBQyxDQUFDO0lBQ04sQ0FBQyxDQUFDLEVBQUUsQ0FBQztJQUVMLENBQUMsQ0FBQyxlQUFlLEdBQUcsVUFBVSxLQUFXLEVBQUUsR0FBRyxJQUFXO1FBQ3JELE9BQU8sQ0FBQyxDQUFDLFFBQVEsRUFBRSxDQUFDLE9BQU8sQ0FBQyxLQUFLLEVBQUUsR0FBRyxJQUFJLENBQUMsQ0FBQyxPQUFPLEVBQUUsQ0FBQztJQUMxRCxDQUFDLENBQUM7SUFFRixDQUFDLENBQUMsZUFBZSxHQUFHLFVBQVUsS0FBVyxFQUFFLEdBQUcsSUFBVztRQUNyRCxPQUFPLENBQUMsQ0FBQyxRQUFRLEVBQUUsQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFFLEdBQUcsSUFBSSxDQUFDLENBQUMsT0FBTyxFQUFFLENBQUM7SUFDekQsQ0FBQyxDQUFDO0lBT1csUUFBQSxPQUFPLEdBQUcsSUFBSSxDQUFDO0lBRzVCLENBQUMsQ0FBQyxFQUFFLENBQUMsTUFBTSxDQUFDO1FBQ1IsTUFBTSxFQUFFLENBQUM7WUFDTCxJQUFJLElBQUksR0FBRyxDQUFDLENBQUM7WUFDYixPQUFPO2dCQUNILE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQztvQkFDYixJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUUsRUFBRTt3QkFDVixJQUFJLENBQUMsRUFBRSxHQUFHLFFBQVEsR0FBRyxDQUFFLEVBQUUsSUFBSSxDQUFFLENBQUM7cUJBQ25DO2dCQUNMLENBQUMsQ0FBQyxDQUFDO1lBQ1AsQ0FBQyxDQUFDO1FBQ04sQ0FBQyxDQUFDLEVBQUU7UUFFSixZQUFZLEVBQUU7WUFDVixPQUFPLElBQUksQ0FBQyxJQUFJLENBQUM7Z0JBQ2IsSUFBSSxhQUFhLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsRUFBRTtvQkFDN0IsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsQ0FBQztpQkFDNUI7WUFDTCxDQUFDLENBQUMsQ0FBQztRQUNQLENBQUM7S0FDSixDQUFDLENBQUM7Ozs7OztJQ2hESCxTQUFnQixPQUFPLENBQUMsR0FBVyxFQUFFLE9BQW1CO1FBQ3BELFFBQVEsQ0FBQyxHQUFHLEVBQUUsT0FBTyxDQUFDLENBQUM7SUFDM0IsQ0FBQztJQUZELDBCQUVDOzs7OztJQ0ZELE1BQU0sQ0FBQyxHQUFHLEdBQUcsSUFBSSxTQUFHLEVBQUUsQ0FBQzs7Ozs7O0lDR3ZCLFNBQWdCLFVBQVUsQ0FBQyxRQUFhLEVBQUUsTUFBVztRQUNqRCxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQ3JDLENBQUM7SUFGRCxnQ0FFQztJQUVELFNBQWdCLFVBQVUsQ0FBQyxHQUFVO1FBQ2pDLFdBQVcsQ0FBQyxDQUFDLEVBQUUsR0FBRyxDQUFDLENBQUM7SUFDeEIsQ0FBQztJQUZELGdDQUVDO0lBRUQsU0FBZ0IsU0FBUyxDQUFDLEdBQVc7UUFDakMsV0FBVyxDQUFDLENBQUMsRUFBRSxHQUFHLENBQUMsQ0FBQztJQUN4QixDQUFDO0lBRkQsOEJBRUM7SUFFRCxTQUFnQixXQUFXLENBQUMsY0FBc0IsRUFBRSxJQUFvQjtRQUNwRSxVQUFVLENBQUMsY0FBYyxFQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQztJQUM1QyxDQUFDO0lBRkQsa0NBRUM7SUFFRCxTQUFnQixVQUFVLENBQUMsTUFBVztRQUNsQyxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUMsU0FBUyxFQUFFLENBQUM7SUFDL0IsQ0FBQztJQUZELGdDQUVDO0lBRUQsU0FBZ0IsU0FBUyxDQUFDLE1BQVc7UUFDakMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDLFVBQVUsRUFBRSxDQUFDO0lBQ2hDLENBQUM7SUFGRCw4QkFFQzs7Ozs7O0lDbUZELE1BQU0sa0JBQWtCO1FBQ2IsT0FBTyxDQUFDLEtBQVU7WUFDckIsSUFBSSxPQUFPLEdBQUcsRUFBRSxDQUFDO1lBRWpCLElBQUksS0FBSyxDQUFDLElBQUksSUFBSSxLQUFLLENBQUMsT0FBTyxFQUFFO2dCQUM3QixPQUFPLElBQUksS0FBSyxDQUFDLElBQUksR0FBRyxJQUFJLEdBQUcsS0FBSyxDQUFDLE9BQU8sQ0FBQzthQUNoRDtpQkFBTTtnQkFDSCxPQUFPLElBQUksS0FBSyxDQUFDLFFBQVEsRUFBRSxHQUFHLFNBQVMsQ0FBQzthQUMzQztZQUVELElBQUksS0FBSyxDQUFDLFFBQVEsSUFBSSxLQUFLLENBQUMsU0FBUyxFQUFFO2dCQUNuQyxPQUFPLElBQUksTUFBTSxHQUFHLENBQUMsS0FBSyxDQUFDLFFBQVEsSUFBSSxLQUFLLENBQUMsU0FBUyxDQUFDLENBQUM7YUFDM0Q7WUFFRCxJQUFJLEtBQUssQ0FBQyxJQUFJLElBQUksS0FBSyxDQUFDLFVBQVUsRUFBRTtnQkFDaEMsT0FBTyxJQUFJLFNBQVMsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLElBQUksS0FBSyxDQUFDLFVBQVUsQ0FBQyxHQUFHLEdBQUcsQ0FBQzthQUNqRTtZQUVELE9BQU8sT0FBTyxDQUFDO1FBQ25CLENBQUM7UUFFTSxLQUFLLENBQUMsS0FBbUI7WUFDNUIsSUFBSSxDQUFDLEtBQUssRUFBRTtnQkFDUixPQUFPLEVBQUUsQ0FBQzthQUNiO1lBRUQsT0FBTyxLQUFLLENBQUMsS0FBSyxJQUFJLEVBQUUsQ0FBQztRQUM3QixDQUFDO0tBQ0o7SUFFRCxTQUFnQixXQUFXO1FBQ3ZCLGNBQWMsQ0FBQyxrQkFBa0IsR0FBRyxHQUFHLEVBQUUsR0FBRyxPQUFPLGtCQUFrQixDQUFDLENBQUMsQ0FBQyxDQUFDO1FBUXpFLE1BQU0sQ0FBQyxPQUFPLEdBQUcsY0FBYyxDQUFDLElBQUksQ0FBQyxjQUFjLENBQUMsQ0FBQztRQUtyRCxNQUFNLEdBQUcsR0FBRyxPQUFPLENBQUMsTUFBTSxFQUFFLENBQUM7UUFPN0IsTUFBTSxnQkFBZ0IsR0FBRyxjQUFjLENBQUMsU0FBUyxDQUFDLE9BQU8sRUFBRSxHQUFHLENBQUMsQ0FBQztRQUtoRSxNQUFNLENBQUMsTUFBTSxFQUFFLGdCQUFnQixDQUFDLENBQUM7UUE0Q2pDLE1BQU0sQ0FBQyxVQUFVLEdBQUcsTUFBTSxDQUFDLFVBQVUsQ0FBQztRQUN0QyxNQUFNLENBQUMsV0FBVyxHQUFHLE1BQU0sQ0FBQyxXQUFXLENBQUM7UUFDeEMsTUFBTSxDQUFDLFlBQVksR0FBRyxNQUFNLENBQUMsWUFBWSxDQUFDO1FBQzFDLE1BQU0sQ0FBQyxhQUFhLEdBQUcsTUFBTSxDQUFDLGFBQWEsQ0FBQztRQWtCNUMsU0FBUyxNQUFNLENBQUMsV0FBZ0IsRUFBRSxNQUFXO1lBRXpDLEtBQUssSUFBSSxRQUFRLElBQUksTUFBTSxFQUFFO2dCQUN6QixXQUFXLENBQUMsUUFBUSxDQUFDLEdBQUcsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDO2FBQzVDO1lBQ0QsT0FBTyxXQUFXLENBQUM7UUFDdkIsQ0FBQztRQUVELE9BQU8sR0FBRyxDQUFDO0lBQ2YsQ0FBQztJQXBHRCxrQ0FvR0M7SUFXRCxNQUFhLG1CQUFtQjtRQVc1QixZQUFtQixTQUFpQixFQUFFLG1CQUFnRDtZQVA1RSxXQUFNLEdBQWdCLEVBQUUsQ0FBQztZQUN6QixZQUFPLEdBQWM7Z0JBQzNCLFNBQVMsRUFBRSxDQUFDO2dCQUNaLGVBQWUsRUFBRSxDQUFDO2FBQ3JCLENBQUM7WUFDTSxjQUFTLEdBQUcsS0FBSyxDQUFDO1lBR3RCLElBQUksQ0FBQyxFQUFFLEdBQUcsQ0FBQyxDQUFDLHNEQUFzRCxDQUFDLENBQUMsU0FBUyxDQUFDLFNBQVMsQ0FBQyxDQUFDO1lBQ3pGLElBQUksQ0FBQyxtQkFBbUIsR0FBRyxtQkFBbUIsQ0FBQztRQUNuRCxDQUFDO1FBRU0sY0FBYyxDQUFDLFNBQTRCO1lBQzlDLElBQUksQ0FBQyxFQUFFLENBQUMsT0FBTyxDQUFDLGtEQUFrRCxDQUFDLENBQUM7WUFDcEUsSUFBSSxDQUFDLEVBQUUsQ0FBQyxNQUFNLENBQUMsZ0NBQWdDLENBQUMsQ0FBQztZQUNqRCxJQUFJLENBQUMsTUFBTSxDQUFDLGdEQUFnRCxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxTQUFTLENBQUMsaUJBQWlCLElBQUksQ0FBQyxDQUFDLEdBQUcsRUFBRSxDQUFDLEdBQUcsUUFBUSxDQUFDLENBQUM7WUFDaEksSUFBSSxDQUFDLE9BQU8sQ0FBQyxlQUFlLEdBQUcsSUFBSSxDQUFDLE9BQU8sQ0FBQyxTQUFTLEdBQUcsQ0FBQyxDQUFDO1lBQzFELElBQUksQ0FBQyxNQUFNLEdBQUcsRUFBRSxDQUFDO1FBQ3JCLENBQUM7UUFFTSxXQUFXLENBQUMsVUFBOEI7WUFDN0MsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE9BQU8sQ0FBQztZQUM3QixJQUFJLENBQUMsTUFBTSxDQUFDLGtDQUFrQyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxPQUFPLENBQUMsU0FBUyxHQUFHLE9BQU8sQ0FBQyxlQUFlLENBQUMsR0FBRyxFQUFFLENBQUMsR0FBRyxHQUFHLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxPQUFPLENBQUMsU0FBUyxHQUFHLEVBQUUsQ0FBQyxDQUFDLENBQUM7WUFDOUosSUFBSSxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQUMsT0FBTyxDQUFDLGVBQWUsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLHNCQUFzQixDQUFDLENBQUMsQ0FBQywwQkFBMEIsQ0FBQyxDQUFDO1FBQ3hHLENBQUM7UUFFTSxZQUFZLENBQUMsTUFBb0M7WUFDcEQsTUFBTSxVQUFVLEdBQUcsTUFBTSxDQUFDLFdBQVcsQ0FBQztZQUN0QyxJQUFJLENBQUMsTUFBTSxDQUFDLDhEQUE4RDtrQkFDcEUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO2tCQUN0RSxVQUFVLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxVQUFVLENBQUMsR0FBRyxlQUFlO2tCQUN0RCxPQUFPLENBQ1osQ0FBQztZQUNGLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDO2dCQUNiLEtBQUssRUFBRSxVQUFVO2dCQUNqQixTQUFTLEVBQUUsQ0FBQztnQkFDWixlQUFlLEVBQUUsQ0FBQzthQUNyQixDQUFDLENBQUM7WUFDSCxJQUFJLENBQUMsU0FBUyxHQUFHLElBQUksQ0FBQztRQUMxQixDQUFDO1FBRU0sU0FBUyxDQUFDLE1BQW9DO1lBQ2pELE1BQU0sS0FBSyxHQUFjLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxFQUFFLENBQUM7WUFDM0MsSUFBSSxDQUFDLE1BQU0sQ0FBQywrREFBK0Q7a0JBQ3JFLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztrQkFDdEUsVUFBVSxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQyxHQUFHLGFBQWE7a0JBQ3JELE1BQU07a0JBQ04sSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO2tCQUN0RSxXQUFXLEdBQUcsQ0FBQyxLQUFLLENBQUMsU0FBUyxHQUFHLEtBQUssQ0FBQyxlQUFlLENBQUMsR0FBRyxHQUFHLEdBQUcsS0FBSyxDQUFDLFNBQVM7a0JBQy9FLHNEQUFzRCxDQUMzRCxDQUFDO1lBQ0YsSUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7Z0JBQzFCLElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUM7YUFDdkI7WUFDRCxJQUFJLENBQUMsU0FBUyxHQUFHLElBQUksQ0FBQztRQUMxQixDQUFDO1FBTU0sUUFBUSxDQUFDLE1BQW9DO1lBR2hELE1BQU0sT0FBTyxHQUFHLENBQUMsTUFBTSxDQUFDLGtCQUFrQixJQUFJLE1BQU0sQ0FBQyxrQkFBa0IsQ0FBQyxNQUFNLEtBQUssQ0FBQyxDQUFDO1lBQ3JGLElBQUksUUFBUSxHQUFHLEVBQUUsQ0FBQztZQUNsQixJQUFJLE9BQU8sRUFBRTtnQkFDVCxRQUFRLElBQUksSUFBSSxDQUFDLG9CQUFvQixDQUFDLE1BQU0sQ0FBQyxDQUFDO2dCQUM5QyxJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDO2FBQ3pCO2lCQUFNO2dCQUNILFFBQVEsSUFBSSxJQUFJLENBQUMsZ0JBQWdCLENBQUMsTUFBTSxDQUFDLENBQUM7Z0JBQzFDLElBQUksQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUM7Z0JBQ3RCLElBQUksQ0FBQyxlQUFlLEVBQUUsQ0FBQztnQkFDdkIsSUFBSSxDQUFDLE9BQU8sQ0FBQyxlQUFlLEVBQUUsQ0FBQzthQUNsQztZQUVELE1BQU0sS0FBSyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEdBQUcsQ0FBQyxDQUFDLENBQUM7WUFDbEQsS0FBSyxDQUFDLFNBQVMsRUFBRSxDQUFDO1lBQ2xCLElBQUksQ0FBQyxPQUFPLENBQUMsU0FBUyxFQUFFLENBQUM7WUFDekIsSUFBSSxDQUFDLE9BQU8sRUFBRTtnQkFDVixLQUFLLENBQUMsZUFBZSxFQUFFLENBQUM7YUFDM0I7UUFDTCxDQUFDO1FBRVMsZUFBZTtZQUNyQixNQUFNLElBQUksR0FBRyxJQUFJLENBQUM7WUFDbEIsSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsNENBQTRDLENBQUMsQ0FBQyxJQUFJLENBQUM7Z0JBQzVELE1BQU0sR0FBRyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztnQkFDcEIsR0FBRyxDQUFDLFFBQVEsQ0FBQyxXQUFXLENBQUMsQ0FBQztnQkFDMUIsTUFBTSxXQUFXLEdBQUcsR0FBRyxDQUFDLElBQUksQ0FBQyxzQkFBc0IsQ0FBQyxDQUFDO2dCQUNyRCxJQUFJLENBQUMsbUJBQW1CLENBQUMsV0FBVyxDQUFDLElBQUksRUFBRSxDQUFDO3FCQUN2QyxJQUFJLENBQUMsVUFBVSxLQUFhO29CQUN6QixLQUFLLEdBQUcsSUFBSSxDQUFDLHdCQUF3QixDQUFDLEtBQUssQ0FBQyxDQUFDO29CQUM3QyxXQUFXLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDO29CQUN4QixHQUFHLENBQUMsSUFBSSxDQUFDLHdDQUF3QyxDQUFDLENBQUMsTUFBTSxFQUFFLENBQUM7b0JBQzVELFdBQVcsQ0FBQyxJQUFJLEVBQUUsQ0FBQztnQkFDdkIsQ0FBQyxDQUFDLENBQUM7WUFDWCxDQUFDLENBQUMsQ0FBQztRQUNQLENBQUM7UUFFUyxvQkFBb0IsQ0FBQyxNQUFvQztZQUMvRCxJQUFJLE1BQU0sR0FBRyxFQUFFLENBQUM7WUFDaEIsSUFBSSxJQUFJLENBQUMsU0FBUyxFQUFFO2dCQUNoQixNQUFNLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDO2dCQUN6QyxJQUFJLENBQUMsU0FBUyxHQUFHLEtBQUssQ0FBQzthQUMxQjtZQUNELE1BQU0sU0FBUyxHQUFHLE1BQU0sQ0FBQyxXQUFXLENBQUM7WUFDckMsSUFBSSxRQUFRLEdBQUcsTUFBTSxHQUFHLGVBQWUsR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxHQUFHLDZCQUE2QixDQUFDO1lBRWpHLFFBQVEsSUFBSSwwQ0FBMEMsQ0FBQztZQUN2RCxPQUFPLFFBQVEsQ0FBQztRQUNwQixDQUFDO1FBRVMsZ0JBQWdCLENBQUMsTUFBb0M7WUFDM0QsSUFBSSxNQUFNLEdBQUcsRUFBRSxDQUFDO1lBQ2hCLElBQUksSUFBSSxDQUFDLFNBQVMsRUFBRTtnQkFDaEIsTUFBTSxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQztnQkFDekMsSUFBSSxDQUFDLFNBQVMsR0FBRyxLQUFLLENBQUM7YUFDMUI7WUFDRCxNQUFNLFNBQVMsR0FBRyxNQUFNLENBQUMsV0FBVyxDQUFDO1lBQ3JDLElBQUksUUFBUSxHQUFHLE1BQU0sR0FBRyxlQUFlLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsR0FBRyw2QkFBNkIsQ0FBQztZQUNqRyxRQUFRLElBQUksdUNBQXVDLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsQ0FBQztZQUM3RSxNQUFNLGtCQUFrQixHQUFJLE1BQU0sQ0FBQyxrQkFBa0IsSUFBSSxFQUFFLENBQUE7WUFDM0QsS0FBSyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLGtCQUFrQixDQUFDLE1BQU0sRUFBRSxDQUFDLEVBQUUsRUFBRTtnQkFDaEQsTUFBTSxXQUFXLEdBQUcsa0JBQWtCLENBQUMsQ0FBQyxDQUFDLENBQUM7Z0JBQzFDLFFBQVEsSUFBSSxpREFBaUQsR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFdBQVcsQ0FBQyxPQUFPLENBQUMsR0FBRyxRQUFRLENBQUM7Z0JBQzVHLFFBQVEsSUFBSSw2TEFBNkwsR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFdBQVcsQ0FBQyxLQUFLLENBQUMsR0FBRyxjQUFjLENBQUM7YUFDL1A7WUFDRCxPQUFPLFFBQVEsQ0FBQztRQUNwQixDQUFDO1FBS1Msd0JBQXdCLENBQUMsS0FBYTtZQUM1QyxNQUFNLEtBQUssR0FBRyxLQUFLLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDO1lBQ2hDLE1BQU0sUUFBUSxHQUFHLENBQUMsSUFBWSxFQUFXLEVBQUUsQ0FBQyxtQ0FBbUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUM7WUFDM0YsT0FBTyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxFQUFFLEtBQUssRUFBRSxFQUFFO2dCQUM3QixJQUFJLEdBQUcsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDO2dCQUNuQixJQUFJLFFBQVEsQ0FBQyxJQUFJLENBQUMsRUFBRTtvQkFDaEIsTUFBTSxVQUFVLEdBQUcsS0FBSyxDQUFDLEtBQUssR0FBRyxDQUFDLENBQUMsS0FBSyxTQUFTLElBQUksQ0FBQyxRQUFRLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDO29CQUNqRixPQUFPLGtFQUFrRSxHQUFHLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxtQ0FBbUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUcsSUFBSSxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLEdBQUcsUUFBUSxDQUFDO2lCQUM3SztnQkFDRCxPQUFPLHlDQUF5QyxJQUFJLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUM7WUFDOUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDO1FBQ2hCLENBQUM7UUFFTyxNQUFNLENBQUMsR0FBVztZQUN0QixPQUFPLGNBQWMsQ0FBQyxJQUFJLEVBQUUsQ0FBQyxVQUFVLENBQUMsR0FBRyxDQUFDLENBQUM7UUFDakQsQ0FBQztRQUVPLE1BQU0sQ0FBQyxJQUFZO1lBQ3ZCLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUM3QyxDQUFDO1FBRU8sSUFBSSxDQUFDLEdBQVE7WUFDakIsT0FBTyxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEdBQUcsUUFBUSxDQUFDO1FBQ2pFLENBQUM7UUFFTyxNQUFNLENBQUMsTUFBYztZQUN6QixJQUFJLENBQUMsR0FBRyxFQUFFLENBQUM7WUFDWCxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsTUFBTSxFQUFFLENBQUMsRUFBRSxFQUFFO2dCQUM3QixDQUFDLElBQUksMEJBQTBCLENBQUM7YUFDbkM7WUFDRCxPQUFPLENBQUMsQ0FBQztRQUNiLENBQUM7S0FDSjtJQTFLRCxrREEwS0MifQ==