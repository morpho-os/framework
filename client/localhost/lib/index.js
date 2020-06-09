"use strict";
define("localhost/lib/event-manager", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.EventManager = void 0;
    class EventManager {
        constructor() {
            this.eventHandlers = {};
        }
        on(eventName, handler) {
            this.eventHandlers[eventName] = this.eventHandlers[eventName] || [];
            this.eventHandlers[eventName].push(handler);
        }
        trigger(eventName, ...args) {
            let handlers = this.eventHandlers[eventName];
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
define("localhost/lib/widget", ["require", "exports", "localhost/lib/event-manager"], function (require, exports, event_manager_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Widget = void 0;
    class Widget extends event_manager_1.EventManager {
        constructor(config) {
            super();
            this.config = this.normalizeConfig(config);
            this.init();
            this.registerEventHandlers();
        }
        init() {
            if (this.config && this.config.el) {
                this.el = $(this.config.el);
            }
        }
        registerEventHandlers() {
        }
        normalizeConfig(config) {
            return config;
        }
    }
    exports.Widget = Widget;
});
define("localhost/lib/message", ["require", "exports", "localhost/lib/widget"], function (require, exports, widget_1) {
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
        registerEventHandlers() {
            super.registerEventHandlers();
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
define("localhost/lib/app", ["require", "exports", "localhost/lib/message"], function (require, exports, message_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.App = void 0;
    class App {
        constructor() {
            this.context = {};
            this.context.pageMessenger = new message_1.PageMessenger({ el: $('#page-messages') });
            this.context = {};
        }
    }
    exports.App = App;
});
define("localhost/lib/base", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.delayedCallback = exports.queryArgs = exports.redirectTo = exports.redirectToHome = exports.redirectToSelf = exports.showUnknownError = exports.Re = exports.isGenerator = exports.isDomNode = exports.isPromise = exports.id = void 0;
    function id(value) {
        return value;
    }
    exports.id = id;
    function isPromise(value) {
        return value && $.isFunction(value.promise);
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
    function redirectTo(uri, storePageInHistory = false) {
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
            timer = setTimeout(function () {
                callback.apply(self, args);
            }, waitMs);
        };
    }
    exports.delayedCallback = delayedCallback;
});
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
String.prototype.encodeHtml = function () {
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
RegExp.escape = function (s) {
    return String(s).replace(/[\\^$*+?.()|[\]{}]/g, '\\$&');
};
define("localhost/lib/error", ["require", "exports"], function (require, exports) {
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
define("localhost/lib/form", ["require", "exports", "localhost/lib/message", "localhost/lib/widget", "localhost/lib/base"], function (require, exports, message_2, widget_2, base_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Form = exports.FieldType = exports.validateEl = exports.defaultValidators = exports.RequiredElValidator = void 0;
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
            return $(this.el[0].elements);
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
            this.elChangeEvents = 'keyup blur change paste cut';
            this.el.attr('novalidate', 'novalidate');
        }
        registerEventHandlers() {
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
            const data = [];
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
            if (result.err) {
                this.handleErrResponse(result.err);
            }
            else if (result.ok) {
                this.handleOkResponse(result.ok);
            }
            else {
                this.invalidResponseError();
            }
        }
        handleOkResponse(responseData) {
            if (responseData.redirect) {
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
define("localhost/lib/i18n", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.tr = void 0;
    function tr(message) {
        return message;
    }
    exports.tr = tr;
});
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJib20udHMiLCJldmVudC1tYW5hZ2VyLnRzIiwid2lkZ2V0LnRzIiwibWVzc2FnZS50cyIsImFwcC50cyIsImJhc2UudHMiLCJlcnJvci50cyIsImZvcm0udHMiLCJpMThuLnRzIiwianF1ZXJ5LWV4dC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7OztJQ09BLE1BQWEsWUFBWTtRQUF6QjtZQUNZLGtCQUFhLEdBQXlELEVBQUUsQ0FBQztRQWtCckYsQ0FBQztRQWhCVSxFQUFFLENBQUMsU0FBaUIsRUFBRSxPQUFnQztZQUN6RCxJQUFJLENBQUMsYUFBYSxDQUFDLFNBQVMsQ0FBQyxHQUFHLElBQUksQ0FBQyxhQUFhLENBQUMsU0FBUyxDQUFDLElBQUksRUFBRSxDQUFDO1lBQ3BFLElBQUksQ0FBQyxhQUFhLENBQUMsU0FBUyxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDO1FBQ2hELENBQUM7UUFFTSxPQUFPLENBQUMsU0FBaUIsRUFBRSxHQUFHLElBQVc7WUFDNUMsSUFBSSxRQUFRLEdBQUcsSUFBSSxDQUFDLGFBQWEsQ0FBQyxTQUFTLENBQUMsQ0FBQztZQUM3QyxJQUFJLENBQUMsUUFBUSxFQUFFO2dCQUNYLE9BQU87YUFDVjtZQUNELEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxRQUFRLENBQUMsTUFBTSxFQUFFLEVBQUUsQ0FBQyxFQUFFO2dCQUN0QyxJQUFJLEtBQUssS0FBSyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxJQUFJLENBQUMsRUFBRTtvQkFDaEMsTUFBTTtpQkFDVDthQUNKO1FBQ0wsQ0FBQztLQUNKO0lBbkJELG9DQW1CQzs7Ozs7O0lDWkQsTUFBc0IsTUFBb0QsU0FBUSw0QkFBWTtRQUsxRixZQUFtQixNQUFlO1lBQzlCLEtBQUssRUFBRSxDQUFDO1lBQ1IsSUFBSSxDQUFDLE1BQU0sR0FBRyxJQUFJLENBQUMsZUFBZSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1lBQzNDLElBQUksQ0FBQyxJQUFJLEVBQUUsQ0FBQztZQUNaLElBQUksQ0FBQyxxQkFBcUIsRUFBRSxDQUFDO1FBQ2pDLENBQUM7UUFFUyxJQUFJO1lBQ1YsSUFBSSxJQUFJLENBQUMsTUFBTSxJQUFJLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBRSxFQUFFO2dCQUMvQixJQUFJLENBQUMsRUFBRSxHQUFHLENBQUMsQ0FBUyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxDQUFDO2FBQ3ZDO1FBQ0wsQ0FBQztRQUVTLHFCQUFxQjtRQUMvQixDQUFDO1FBRVMsZUFBZSxDQUFDLE1BQWU7WUFDckMsT0FBTyxNQUFNLENBQUM7UUFDbEIsQ0FBQztLQUNKO0lBeEJELHdCQXdCQzs7Ozs7O0lDNUJELElBQVksV0FNWDtJQU5ELFdBQVksV0FBVztRQUNuQiwrQ0FBUyxDQUFBO1FBQ1QsbURBQVcsQ0FBQTtRQUNYLDZDQUFRLENBQUE7UUFDUiwrQ0FBUyxDQUFBO1FBQ1QsNENBQW9DLENBQUE7SUFDeEMsQ0FBQyxFQU5XLFdBQVcsR0FBWCxtQkFBVyxLQUFYLG1CQUFXLFFBTXRCO0lBYUQsTUFBYSxhQUFjLFNBQVEsZUFBTTtRQUMzQixnQkFBZ0I7WUFDdEIsT0FBTyxJQUFJLENBQUMsVUFBVSxFQUFFLENBQUMsTUFBTSxDQUFDO1FBQ3BDLENBQUM7UUFFUyxVQUFVO1lBQ2hCLE9BQU8sSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7UUFDbEMsQ0FBQztRQUVTLHFCQUFxQjtZQUMzQixLQUFLLENBQUMscUJBQXFCLEVBQUUsQ0FBQztZQUM5QixJQUFJLENBQUMsMkJBQTJCLEVBQUUsQ0FBQztRQUN2QyxDQUFDO1FBRVMsMkJBQTJCO1lBQ2pDLE1BQU0sSUFBSSxHQUFHLElBQUksQ0FBQztZQUVsQixTQUFTLGNBQWMsQ0FBQyxHQUFXLEVBQUUsUUFBcUM7Z0JBQ3RFLEdBQUcsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLENBQUM7WUFDMUIsQ0FBQztZQUVELFNBQVMseUJBQXlCO2dCQUM5QixjQUFjLENBQUMsSUFBSSxDQUFDLEVBQUUsRUFBRTtvQkFDcEIsSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsTUFBTSxFQUFFLENBQUM7b0JBQ25DLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxFQUFFLENBQUM7Z0JBQ25CLENBQUMsQ0FBQyxDQUFDO1lBQ1AsQ0FBQztZQUVELFNBQVMsb0JBQW9CLENBQUMsUUFBZ0I7Z0JBQzFDLElBQUksSUFBSSxDQUFDLGdCQUFnQixFQUFFLEtBQUssQ0FBQyxFQUFFO29CQUMvQix5QkFBeUIsRUFBRSxDQUFDO2lCQUMvQjtxQkFBTTtvQkFDSCxNQUFNLGlCQUFpQixHQUFHLFFBQVEsQ0FBQyxPQUFPLENBQUMsV0FBVyxDQUFDLENBQUM7b0JBQ3hELElBQUksaUJBQWlCLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7d0JBQy9DLGNBQWMsQ0FBQyxpQkFBaUIsRUFBRTs0QkFDOUIsaUJBQWlCLENBQUMsTUFBTSxFQUFFLENBQUM7d0JBQy9CLENBQUMsQ0FBQyxDQUFDO3FCQUNOO3lCQUFNO3dCQUNILGNBQWMsQ0FBQyxRQUFRLEVBQUU7NEJBQ3JCLFFBQVEsQ0FBQyxNQUFNLEVBQUUsQ0FBQzt3QkFDdEIsQ0FBQyxDQUFDLENBQUM7cUJBQ047aUJBQ0o7WUFDTCxDQUFDO1lBRUQsSUFBSSxDQUFDLEVBQUUsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLGNBQWMsRUFBRTtnQkFDaEMsb0JBQW9CLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1lBQ3BELENBQUMsQ0FBQyxDQUFDO1lBQ0gsVUFBVSxDQUFDO2dCQUNQLHlCQUF5QixFQUFFLENBQUM7WUFDaEMsQ0FBQyxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ2IsQ0FBQztLQUNKO0lBcERELHNDQW9EQztJQUVELFNBQWdCLGFBQWEsQ0FBQyxPQUFnQjtRQUMxQyxJQUFJLElBQUksR0FBRyxPQUFPLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxDQUFDO1FBQ3JDLElBQUksR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNqQyxPQUFPLFdBQVcsQ0FBQyxJQUFJLEVBQUUsZ0JBQWdCLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7SUFDN0QsQ0FBQztJQUpELHNDQUlDO0lBRUQsU0FBUyxXQUFXLENBQUMsSUFBWSxFQUFFLElBQVk7UUFDM0MsT0FBTyxjQUFjLEdBQUcsSUFBSSxDQUFDLFdBQVcsRUFBRSxDQUFDLFVBQVUsRUFBRSxHQUFHLElBQUksR0FBRyxJQUFJLEdBQUcsUUFBUSxDQUFDO0lBQ3JGLENBQUM7SUFFRCxTQUFnQixnQkFBZ0IsQ0FBQyxJQUFpQjtRQWU5QyxPQUFPLFdBQVcsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUM3QixDQUFDO0lBaEJELDRDQWdCQztJQUVELE1BQWEsT0FBTztRQUNoQixZQUFtQixJQUFpQixFQUFTLElBQVksRUFBUyxPQUFpQixFQUFFO1lBQWxFLFNBQUksR0FBSixJQUFJLENBQWE7WUFBUyxTQUFJLEdBQUosSUFBSSxDQUFRO1lBQVMsU0FBSSxHQUFKLElBQUksQ0FBZTtRQUNyRixDQUFDO1FBRU0sT0FBTyxDQUFDLElBQWlCO1lBQzVCLE9BQU8sSUFBSSxDQUFDLElBQUksS0FBSyxJQUFJLENBQUM7UUFDOUIsQ0FBQztLQUNKO0lBUEQsMEJBT0M7SUFFRCxNQUFhLFlBQWEsU0FBUSxPQUFPO1FBQ3JDLFlBQVksSUFBWSxFQUFFLE9BQWlCLEVBQUU7WUFDekMsS0FBSyxDQUFDLFdBQVcsQ0FBQyxLQUFLLEVBQUUsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ3pDLENBQUM7S0FDSjtJQUpELG9DQUlDO0lBRUQsTUFBYSxjQUFlLFNBQVEsT0FBTztRQUN2QyxZQUFZLElBQVksRUFBRSxPQUFpQixFQUFFO1lBQ3pDLEtBQUssQ0FBQyxXQUFXLENBQUMsT0FBTyxFQUFFLElBQUksRUFBRSxJQUFJLENBQUMsQ0FBQztRQUMzQyxDQUFDO0tBQ0o7SUFKRCx3Q0FJQztJQUVELE1BQWEsV0FBWSxTQUFRLE9BQU87UUFDcEMsWUFBWSxJQUFZLEVBQUUsT0FBaUIsRUFBRTtZQUN6QyxLQUFLLENBQUMsV0FBVyxDQUFDLE9BQU8sRUFBRSxJQUFJLEVBQUUsSUFBSSxDQUFDLENBQUM7UUFDM0MsQ0FBQztLQUNKO0lBSkQsa0NBSUM7SUFFRCxNQUFhLFlBQWEsU0FBUSxPQUFPO1FBQ3JDLFlBQVksSUFBWSxFQUFFLE9BQWlCLEVBQUU7WUFDekMsS0FBSyxDQUFDLFdBQVcsQ0FBQyxLQUFLLEVBQUUsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ3pDLENBQUM7S0FDSjtJQUpELG9DQUlDOzs7Ozs7SUMxSUQsTUFBYSxHQUFHO1FBR1o7WUFGTyxZQUFPLEdBQXdCLEVBQUUsQ0FBQztZQUdyQyxJQUFJLENBQUMsT0FBTyxDQUFDLGFBQWEsR0FBRyxJQUFJLHVCQUFhLENBQUMsRUFBQyxFQUFFLEVBQUUsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLEVBQUMsQ0FBQyxDQUFDO1lBQzFFLElBQUksQ0FBQyxPQUFPLEdBQUcsRUFBRSxDQUFBO1FBQ3JCLENBQUM7S0FDSjtJQVBELGtCQU9DOzs7Ozs7SUNKRCxTQUFnQixFQUFFLENBQUMsS0FBVTtRQUN6QixPQUFPLEtBQUssQ0FBQztJQUNqQixDQUFDO0lBRkQsZ0JBRUM7SUFFRCxTQUFnQixTQUFTLENBQUMsS0FBVTtRQUNoQyxPQUFPLEtBQUssSUFBSSxDQUFDLENBQUMsVUFBVSxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsQ0FBQztJQUNoRCxDQUFDO0lBRkQsOEJBRUM7SUFHRCxTQUFnQixTQUFTLENBQUMsR0FBUTtRQUM5QixPQUFPLEdBQUcsQ0FBQyxRQUFRLEdBQUcsQ0FBQyxDQUFDO0lBQzVCLENBQUM7SUFGRCw4QkFFQztJQUVELFNBQWdCLFdBQVcsQ0FBQyxFQUFZO1FBQ3BDLE9BQWEsRUFBRSxDQUFDLFdBQVksQ0FBQyxJQUFJLEtBQUssbUJBQW1CLENBQUM7SUFDOUQsQ0FBQztJQUZELGtDQUVDO0lBRUQsTUFBYSxFQUFFOztJQUFmLGdCQUVDO0lBRDBCLFFBQUssR0FBRyxlQUFlLENBQUM7SUFNbkQsU0FBZ0IsZ0JBQWdCLENBQUMsT0FBZ0I7UUFFN0MsS0FBSyxDQUFDLHVDQUF1QyxDQUFDLENBQUM7SUFDbkQsQ0FBQztJQUhELDRDQUdDO0lBRUQsU0FBZ0IsY0FBYztRQUUxQixNQUFNLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBRSxDQUFDO0lBQzdCLENBQUM7SUFIRCx3Q0FHQztJQUVELFNBQWdCLGNBQWM7UUFHMUIsVUFBVSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQ3BCLENBQUM7SUFKRCx3Q0FJQztJQUVELFNBQWdCLFVBQVUsQ0FBQyxHQUFXLEVBQUUsa0JBQWtCLEdBQUcsS0FBSztRQUM5RCxJQUFJLGtCQUFrQixFQUFFO1lBQ3BCLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxHQUFHLEdBQUcsQ0FBQztTQUM5QjthQUFNO1lBQ0gsTUFBTSxDQUFDLFFBQVEsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUM7U0FDaEM7SUFDTCxDQUFDO0lBTkQsZ0NBTUM7SUFHRCxTQUFnQixTQUFTO1FBQ3JCLE1BQU0sTUFBTSxHQUFHLENBQUMsS0FBYSxFQUFVLEVBQUUsQ0FBQyxrQkFBa0IsQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUFDLEtBQUssRUFBRSxHQUFHLENBQUMsQ0FBQyxDQUFDO1FBRXhGLE1BQU0sTUFBTSxHQUFHLHFCQUFxQixDQUFDO1FBQ3JDLElBQUksU0FBUyxHQUF1QixFQUFFLEVBQ2xDLElBQUksQ0FBQztRQUVULE9BQU8sSUFBSSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsRUFBRTtZQUMvQyxJQUFJLEdBQUcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQ3JCLEtBQUssR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFLNUIsSUFBSSxHQUFHLElBQUksU0FBUyxFQUFFO2dCQUNsQixTQUFTO2FBQ1o7WUFDRCxTQUFTLENBQUMsR0FBRyxDQUFDLEdBQUcsS0FBSyxDQUFDO1NBQzFCO1FBRUQsT0FBTyxTQUFTLENBQUM7SUFDckIsQ0FBQztJQXJCRCw4QkFxQkM7SUFJRCxTQUFnQixlQUFlLENBQUMsUUFBa0IsRUFBRSxNQUFjO1FBQzlELElBQUksS0FBSyxHQUFHLENBQUMsQ0FBQztRQUNkLE9BQU87WUFDSCxNQUFNLElBQUksR0FBRyxJQUFJLENBQUM7WUFDbEIsTUFBTSxJQUFJLEdBQUcsU0FBUyxDQUFDO1lBQ3ZCLFlBQVksQ0FBQyxLQUFLLENBQUMsQ0FBQztZQUNwQixLQUFLLEdBQUcsVUFBVSxDQUFDO2dCQUNmLFFBQVEsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO1lBQy9CLENBQUMsRUFBRSxNQUFNLENBQUMsQ0FBQztRQUNmLENBQUMsQ0FBQztJQUNOLENBQUM7SUFWRCwwQ0FVQzs7QUxqRkQsSUFBSSxDQUFDLEdBQUcsR0FBRyxRQUFRLENBQUM7QUFFcEIsSUFBSSxDQUFDLFVBQVUsR0FBRyxVQUFVLEdBQVcsRUFBRSxZQUFvQixDQUFDO0lBQzFELE1BQU0sRUFBRSxHQUFHLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxFQUFFLFNBQVMsQ0FBQyxDQUFDO0lBQ25DLE9BQU8sSUFBSSxDQUFDLEtBQUssQ0FBQyxHQUFHLEdBQUcsRUFBRSxDQUFDLEdBQUcsRUFBRSxDQUFDO0FBQ3JDLENBQUMsQ0FBQztBQUNGLElBQUksQ0FBQyxpQkFBaUIsR0FBRyxVQUFVLEdBQVc7SUFDMUMsT0FBTyxHQUFHLEdBQUcsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO0FBQzNCLENBQUMsQ0FBQztBQUNGLElBQUksQ0FBQyxvQkFBb0IsR0FBRyxVQUFVLEdBQVc7SUFDN0MsT0FBTyxHQUFHLEdBQUcsSUFBSSxDQUFDLEdBQUcsQ0FBQztBQUMxQixDQUFDLENBQUM7QUFDRixJQUFJLENBQUMsY0FBYyxHQUFHLFVBQVUsR0FBVztJQUN2QyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBRyxDQUFDLElBQUksSUFBSSxDQUFDLEdBQUcsQ0FBQztBQUNyQyxDQUFDLENBQUM7QUFDRixJQUFJLENBQUMsV0FBVyxHQUFHLFVBQVUsQ0FBUyxFQUFFLENBQVM7SUFDN0MsT0FBTyxJQUFJLENBQUMsY0FBYyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztBQUN0QyxDQUFDLENBQUM7QUFHRixJQUFJLENBQUMsSUFBSSxHQUFHLFVBQVUsQ0FBUyxFQUFFLElBQVk7SUFDekMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLENBQUM7QUFDeEMsQ0FBQyxDQUFDO0FBS0YsTUFBTSxDQUFDLFNBQVMsQ0FBQyxVQUFVLEdBQUc7SUFDMUIsTUFBTSxTQUFTLEdBQUc7UUFDZCxHQUFHLEVBQUUsT0FBTztRQUNaLEdBQUcsRUFBRSxNQUFNO1FBQ1gsR0FBRyxFQUFFLE1BQU07UUFFWCxHQUFHLEVBQUUsUUFBUTtRQUNiLEdBQUcsRUFBRSxPQUFPO0tBQ2YsQ0FBQztJQUNGLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxVQUFVLEVBQUUsVUFBVSxDQUFTO1FBQy9DLE9BQWEsU0FBVSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQy9CLENBQUMsQ0FBQyxDQUFDO0FBQ1AsQ0FBQyxDQUFDO0FBRUYsTUFBTSxDQUFDLFNBQVMsQ0FBQyxRQUFRLEdBQUc7SUFFeEIsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBRSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUM7QUFDeEQsQ0FBQyxDQUFDO0FBRUYsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUcsVUFBd0IsSUFBYyxFQUFFLE1BQThCO0lBQzVGLElBQUksR0FBRyxHQUFHLElBQUksQ0FBQztJQUNmLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxHQUFXLEVBQUUsS0FBYSxFQUFFLEVBQUU7UUFDeEMsR0FBRyxHQUFHLEdBQUcsQ0FBQyxPQUFPLENBQUMsR0FBRyxHQUFHLEtBQUssR0FBRyxHQUFHLEVBQUUsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQ3JFLENBQUMsQ0FBQyxDQUFDO0lBQ0gsT0FBTyxHQUFHLENBQUM7QUFDZixDQUFDLENBQUE7QUFFRCxNQUFNLENBQUMsU0FBUyxDQUFDLEtBQUssR0FBRztJQUNyQixPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsUUFBUSxFQUFFLE1BQU0sQ0FBQyxDQUFDO0FBQzFDLENBQUMsQ0FBQztBQUNGLE1BQU0sQ0FBQyxTQUFTLENBQUMsVUFBVSxHQUFHLFVBQVUsTUFBYyxFQUFFLE9BQWU7SUFDbkUsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDLE1BQU0sQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQztBQUM1QyxDQUFDLENBQUM7QUFNRixNQUFNLENBQUMsTUFBTSxHQUFHLFVBQVUsQ0FBUztJQUMvQixPQUFPLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMscUJBQXFCLEVBQUUsTUFBTSxDQUFDLENBQUM7QUFDNUQsQ0FBQyxDQUFDOzs7OztJTXRFRixNQUFhLFNBQVUsU0FBUSxLQUFLO1FBR2hDLFlBQW1CLE9BQWU7WUFDOUIsS0FBSyxDQUFDLE9BQU8sQ0FBQyxDQUFDO1lBREEsWUFBTyxHQUFQLE9BQU8sQ0FBUTtZQUU5QixJQUFJLENBQUMsSUFBSSxHQUFHLFdBQVcsQ0FBQztZQUN4QixJQUFJLENBQUMsT0FBTyxHQUFHLE9BQU8sQ0FBQztRQUUzQixDQUFDO1FBRU0sUUFBUTtZQUNYLE9BQU8sSUFBSSxDQUFDLElBQUksR0FBRyxJQUFJLEdBQUcsSUFBSSxDQUFDLE9BQU8sQ0FBQztRQUMzQyxDQUFDO0tBQ0o7SUFiRCw4QkFhQztJQUVELE1BQWEsdUJBQXdCLFNBQVEsU0FBUztLQUNyRDtJQURELDBEQUNDO0lBRUQsTUFBYSx3QkFBeUIsU0FBUSxTQUFTO0tBQ3REO0lBREQsNERBQ0M7Ozs7OztJQ1pELE1BQWEsbUJBQW1CO1FBR3JCLFFBQVEsQ0FBQyxHQUFXO1lBQ3ZCLElBQUksSUFBSSxDQUFDLFlBQVksQ0FBQyxHQUFHLENBQUMsRUFBRTtnQkFDeEIsSUFBSSxJQUFJLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksRUFBRSxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUU7b0JBQ3JDLE9BQU8sQ0FBQyxtQkFBbUIsQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDO2lCQUNsRDthQUNKO1lBQ0QsT0FBTyxFQUFFLENBQUM7UUFDZCxDQUFDOztJQVZMLGtEQVdDO0lBVjBCLHFDQUFpQixHQUFHLHdCQUF3QixDQUFDO0lBZ0J4RSxTQUFnQixpQkFBaUI7UUFDN0IsT0FBTztZQUNILElBQUksbUJBQW1CLEVBQUU7U0FDNUIsQ0FBQztJQUNOLENBQUM7SUFKRCw4Q0FJQztJQUVELFNBQWdCLFVBQVUsQ0FBQyxHQUFXLEVBQUUsVUFBMEI7UUFDOUQsSUFBSSxDQUFDLFVBQVUsRUFBRTtZQUNiLFVBQVUsR0FBRyxpQkFBaUIsRUFBRSxDQUFDO1NBQ3BDO1FBQ0QsSUFBSSxNQUFNLEdBQWEsRUFBRSxDQUFDO1FBQzFCLFVBQVUsQ0FBQyxPQUFPLENBQUMsVUFBVSxTQUFzQjtZQUMvQyxNQUFNLEdBQUcsTUFBTSxDQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7UUFDcEQsQ0FBQyxDQUFDLENBQUM7UUFDSCxPQUFPLE1BQU0sQ0FBQztJQUNsQixDQUFDO0lBVEQsZ0NBU0M7SUFFRCxJQUFZLFNBYVg7SUFiRCxXQUFZLFNBQVM7UUFDakIsOEJBQWlCLENBQUE7UUFDakIsa0NBQXFCLENBQUE7UUFDckIsMEJBQWEsQ0FBQTtRQUNiLDhCQUFpQixDQUFBO1FBQ2pCLDRCQUFlLENBQUE7UUFDZixrQ0FBcUIsQ0FBQTtRQUNyQiw0QkFBZSxDQUFBO1FBQ2YsNEJBQWUsQ0FBQTtRQUNmLDhCQUFpQixDQUFBO1FBQ2pCLDhCQUFpQixDQUFBO1FBQ2pCLGtDQUFxQixDQUFBO1FBQ3JCLCtCQUFrQixDQUFBO0lBQ3RCLENBQUMsRUFiVyxTQUFTLEdBQVQsaUJBQVMsS0FBVCxpQkFBUyxRQWFwQjtJQUVELE1BQWEsSUFBaUMsU0FBUSxlQUFtQjtRQVE5RCxNQUFNLENBQUMsT0FBTyxDQUFDLEdBQVc7WUFDN0IsSUFBVSxHQUFHLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBRSxDQUFDLE1BQU0sQ0FBQyxLQUFLLFVBQVUsRUFBRTtnQkFDMUMsT0FBTyxHQUFHLENBQUMsRUFBRSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQzthQUNyQztZQUNELE9BQU8sR0FBRyxDQUFDLEdBQUcsRUFBRSxDQUFDO1FBQ3JCLENBQUM7UUFFTSxNQUFNLENBQUMsWUFBWSxDQUFDLEdBQVc7WUFDbEMsT0FBTyxHQUFHLENBQUMsRUFBRSxDQUFDLFlBQVksQ0FBQyxDQUFDO1FBQ2hDLENBQUM7UUFFTSxHQUFHO1lBQ04sT0FBTyxDQUFDLENBQVEsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUUsQ0FBQyxRQUFRLENBQUMsQ0FBQztRQUMxQyxDQUFDO1FBRU0sYUFBYTtZQUNoQixPQUFPLElBQUksQ0FBQyxHQUFHLEVBQUUsQ0FBQyxNQUFNLENBQUM7Z0JBQ3JCLE1BQU0sR0FBRyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztnQkFDcEIsT0FBTyxHQUFHLENBQUMsRUFBRSxDQUFDLGVBQWUsQ0FBQyxDQUFDO1lBQ25DLENBQUMsQ0FBQyxDQUFDO1FBQ1AsQ0FBQztRQUVNLFFBQVE7WUFDWCxJQUFJLENBQUMsWUFBWSxFQUFFLENBQUM7WUFDcEIsSUFBSSxNQUFNLEdBQW9DLEVBQUUsQ0FBQztZQUNqRCxJQUFJLENBQUMsYUFBYSxFQUFFLENBQUMsSUFBSSxDQUFDO2dCQUN0QixNQUFNLEdBQUcsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7Z0JBQ3BCLE1BQU0sUUFBUSxHQUFHLFVBQVUsQ0FBQyxHQUFHLENBQUMsQ0FBQztnQkFDakMsSUFBSSxRQUFRLENBQUMsTUFBTSxFQUFFO29CQUNqQixNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBRyxFQUFFLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFhLEVBQUUsRUFBRSxHQUFHLE9BQU8sSUFBSSxzQkFBWSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2lCQUM1RjtZQUNMLENBQUMsQ0FBQyxDQUFDO1lBQ0gsSUFBSSxNQUFNLENBQUMsTUFBTSxFQUFFO2dCQUNmLElBQUksQ0FBQyxVQUFVLENBQUMsTUFBTSxDQUFDLENBQUM7Z0JBQ3hCLE9BQU8sS0FBSyxDQUFDO2FBQ2hCO1lBQ0QsT0FBTyxJQUFJLENBQUM7UUFDaEIsQ0FBQztRQUVNLFVBQVU7WUFDYixNQUFNLElBQUksR0FBRyxJQUFJLENBQUM7WUFDbEIsT0FBTyxJQUFJLENBQUMsR0FBRyxFQUFFLENBQUMsTUFBTSxDQUFDO2dCQUNyQixPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDO1lBQ2xELENBQUMsQ0FBQyxDQUFDO1FBQ1AsQ0FBQztRQUVNLFNBQVM7WUFDWixPQUFPLElBQUksQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsQ0FBQztRQUNsRCxDQUFDO1FBTU0sWUFBWTtZQUNmLElBQUksQ0FBQyxVQUFVLEVBQUUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxLQUFhLEVBQUUsRUFBZSxFQUFFLEVBQUU7Z0JBQ3RELElBQUksQ0FBQyxjQUFjLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUM7WUFDL0IsQ0FBQyxDQUFDLENBQUM7WUFDSCxJQUFJLENBQUMsc0JBQXNCLEVBQUUsQ0FBQyxNQUFNLEVBQUUsQ0FBQztZQUN2QyxJQUFJLENBQUMsRUFBRSxDQUFDLFdBQVcsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUM7UUFDOUMsQ0FBQztRQUVNLE1BQU07WUFDVCxJQUFJLENBQUMsWUFBWSxFQUFFLENBQUM7WUFDcEIsSUFBSSxJQUFJLENBQUMsY0FBYyxFQUFFO2dCQUNyQixJQUFJLENBQUMsSUFBSSxFQUFFLENBQUM7YUFDZjtpQkFBTSxJQUFJLElBQUksQ0FBQyxRQUFRLEVBQUUsRUFBRTtnQkFDeEIsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDO2FBQ2Y7UUFDTCxDQUFDO1FBRU0sSUFBSTtZQUNQLElBQUksQ0FBQyxzQkFBc0IsRUFBRSxDQUFDO1lBQzlCLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsR0FBRyxFQUFFLEVBQUUsSUFBSSxDQUFDLFFBQVEsRUFBRSxDQUFDLENBQUM7UUFDMUQsQ0FBQztRQUtNLFVBQVUsQ0FBQyxNQUFzRDtZQUNwRSxJQUFJLFVBQVUsR0FBbUIsRUFBRSxDQUFDO1lBQ3BDLE1BQU0sQ0FBQyxPQUFPLENBQUMsQ0FBQyxHQUE0QyxFQUFFLEVBQUU7Z0JBQzVELElBQUksS0FBSyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBRTtvQkFDcEIsTUFBTSxDQUFDLEdBQUcsRUFBRSxRQUFRLENBQUMsR0FBRyxHQUFHLENBQUM7b0JBQzVCLElBQUksQ0FBQyxZQUFZLENBQUMsR0FBRyxFQUFFLFFBQVEsQ0FBQyxDQUFDO2lCQUNwQztxQkFBTTtvQkFDSCxVQUFVLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO2lCQUN4QjtZQUNMLENBQUMsQ0FBQyxDQUFDO1lBQ0gsSUFBSSxDQUFDLGNBQWMsQ0FBQyxVQUFVLENBQUMsQ0FBQztZQUNoQyxJQUFJLENBQUMsa0JBQWtCLEVBQUUsQ0FBQztRQUM5QixDQUFDO1FBRU0sTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFjO1lBQ2xDLE1BQU0sUUFBUSxHQUFHLEdBQUcsRUFBRTtnQkFDbEIsTUFBTSxRQUFRLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQztnQkFDckMsT0FBTyxRQUFRLEtBQUssU0FBUyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxXQUFXLEVBQUUsQ0FBQztZQUNoRSxDQUFDLENBQUM7WUFDRixJQUFJLGFBQWEsQ0FBQztZQUNsQixRQUFRLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUU7Z0JBQ3ZCLEtBQUssT0FBTztvQkFDUixhQUFhLEdBQUcsUUFBUSxFQUFFLENBQUM7b0JBQzNCLFFBQVEsYUFBYSxFQUFFO3dCQUNuQixLQUFLLE1BQU07NEJBQ1AsT0FBTyxTQUFTLENBQUMsU0FBUyxDQUFDO3dCQUMvQixLQUFLLE9BQU87NEJBQ1IsT0FBTyxTQUFTLENBQUMsS0FBSyxDQUFDO3dCQUMzQixLQUFLLFFBQVE7NEJBQ1QsT0FBTyxTQUFTLENBQUMsTUFBTSxDQUFDO3dCQUM1QixLQUFLLFFBQVE7NEJBQ1QsT0FBTyxTQUFTLENBQUMsTUFBTSxDQUFDO3dCQUM1QixLQUFLLFVBQVU7NEJBQ1gsT0FBTyxTQUFTLENBQUMsUUFBUSxDQUFDO3dCQUM5QixLQUFLLE1BQU07NEJBQ1AsT0FBTyxTQUFTLENBQUMsSUFBSSxDQUFDO3dCQUMxQixLQUFLLFFBQVE7NEJBQ1QsT0FBTyxTQUFTLENBQUMsTUFBTSxDQUFDO3dCQUM1QixLQUFLLE9BQU87NEJBQ1IsT0FBTyxTQUFTLENBQUMsS0FBSyxDQUFDO3dCQUMzQixLQUFLLFVBQVU7NEJBQ1gsT0FBTyxTQUFTLENBQUMsUUFBUSxDQUFDO3dCQUM5QixLQUFLLE9BQU87NEJBQ1IsT0FBTyxTQUFTLENBQUMsS0FBSyxDQUFDO3FCQUM5QjtvQkFDRCxNQUFNO2dCQUNWLEtBQUssVUFBVTtvQkFDWCxPQUFPLFNBQVMsQ0FBQyxRQUFRLENBQUM7Z0JBQzlCLEtBQUssUUFBUTtvQkFDVCxPQUFPLFNBQVMsQ0FBQyxNQUFNLENBQUM7Z0JBQzVCLEtBQUssUUFBUTtvQkFDVCxhQUFhLEdBQUcsUUFBUSxFQUFFLENBQUM7b0JBQzNCLElBQUksYUFBYSxLQUFLLEVBQUUsSUFBSSxhQUFhLEtBQUssUUFBUSxFQUFFO3dCQUNwRCxPQUFPLFNBQVMsQ0FBQyxNQUFNLENBQUM7cUJBQzNCO29CQUNELElBQUksYUFBYSxLQUFLLFFBQVEsRUFBRTt3QkFDNUIsT0FBTyxTQUFTLENBQUMsTUFBTSxDQUFDO3FCQUMzQjtvQkFDRCxNQUFNO2FBQ2I7WUFDRCxNQUFNLElBQUksS0FBSyxDQUFDLG9CQUFvQixDQUFDLENBQUM7UUFDMUMsQ0FBQztRQUVTLGNBQWMsQ0FBQyxNQUFzQjtZQUMzQyxJQUFJLE1BQU0sQ0FBQyxNQUFNLEVBQUU7Z0JBQ2YsTUFBTSxRQUFRLEdBQVcsaUNBQWlDLEdBQUcsTUFBTSxDQUFDLEdBQUcsQ0FBQyx1QkFBYSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLFFBQVEsQ0FBQztnQkFDN0csSUFBSSxDQUFDLHNCQUFzQixFQUFFO3FCQUN4QixPQUFPLENBQUMsUUFBUSxDQUFDLENBQUM7YUFDMUI7WUFDRCxJQUFJLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUM7UUFDM0MsQ0FBQztRQUVTLFlBQVksQ0FBQyxHQUFXLEVBQUUsTUFBc0I7WUFDdEQsTUFBTSxlQUFlLEdBQUcsSUFBSSxDQUFDLGVBQWUsQ0FBQztZQUM3QyxHQUFHLENBQUMsUUFBUSxDQUFDLGVBQWUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLEdBQUcsSUFBSSxDQUFDLG1CQUFtQixDQUFDLENBQUMsUUFBUSxDQUFDLGVBQWUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxXQUFXLENBQUMsQ0FBQztZQUN0SCxHQUFHLENBQUMsS0FBSyxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsdUJBQWEsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1FBQ3BELENBQUM7UUFFUyxjQUFjLENBQUMsR0FBVztZQUNoQyxNQUFNLFVBQVUsR0FBRyxHQUFHLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxHQUFHLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDO1lBQ2pHLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBRyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUMsTUFBTSxFQUFFO2dCQUNyRCxVQUFVLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsQ0FBQyxXQUFXLENBQUMsV0FBVyxDQUFDLENBQUM7YUFDekU7WUFDRCxHQUFHLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLE1BQU0sRUFBRSxDQUFDO1FBQ2hDLENBQUM7UUFFUyxzQkFBc0I7WUFDNUIsTUFBTSxpQkFBaUIsR0FBRyxJQUFJLENBQUMsNEJBQTRCLENBQUM7WUFDNUQsSUFBSSxZQUFZLEdBQUcsSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFHLGlCQUFpQixDQUFDLENBQUM7WUFDekQsSUFBSSxDQUFDLFlBQVksQ0FBQyxNQUFNLEVBQUU7Z0JBQ3RCLFlBQVksR0FBRyxDQUFDLENBQUMsY0FBYyxHQUFHLGlCQUFpQixHQUFHLFVBQVUsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUM7YUFDeEY7WUFDRCxPQUFPLFlBQVksQ0FBQztRQUN4QixDQUFDO1FBRVMsSUFBSTtZQUNWLEtBQUssQ0FBQyxJQUFJLEVBQUUsQ0FBQztZQUNiLElBQUksQ0FBQyxjQUFjLEdBQUcsS0FBSyxDQUFDO1lBQzVCLElBQUksQ0FBQyxtQkFBbUIsR0FBRyxZQUFZLENBQUM7WUFDeEMsSUFBSSxDQUFDLDRCQUE0QixHQUFHLFVBQVUsQ0FBQztZQUMvQyxJQUFJLENBQUMsZUFBZSxHQUFHLElBQUksQ0FBQyxzQkFBc0IsQ0FBQztZQUNuRCxJQUFJLENBQUMsY0FBYyxHQUFHLDZCQUE2QixDQUFDO1lBQ3BELElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLFlBQVksRUFBRSxZQUFZLENBQUMsQ0FBQztRQUM3QyxDQUFDO1FBRVMscUJBQXFCO1lBQzNCLElBQUksQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLFFBQVEsRUFBRSxHQUFHLEVBQUU7Z0JBQ3RCLElBQUksQ0FBQyxNQUFNLEVBQUUsQ0FBQztnQkFDZCxPQUFPLEtBQUssQ0FBQztZQUNqQixDQUFDLENBQUMsQ0FBQztZQUNILE1BQU0sSUFBSSxHQUFHLElBQUksQ0FBQztZQUNsQixJQUFJLENBQUMsYUFBYSxFQUFFLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxjQUFjLEVBQUU7Z0JBQ3pDLE1BQU0sR0FBRyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztnQkFDcEIsSUFBSSxHQUFHLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsRUFBRTtvQkFDcEMsSUFBSSxDQUFDLGNBQWMsQ0FBQyxHQUFHLENBQUMsQ0FBQztpQkFDNUI7WUFDTCxDQUFDLENBQUMsQ0FBQztRQUNQLENBQUM7UUFFUyxZQUFZLENBQUMsR0FBVyxFQUFFLFdBQW1CO1lBQ25ELE1BQU0sWUFBWSxHQUFHLElBQUksQ0FBQyxZQUFZLEVBQUUsQ0FBQztZQUN6QyxZQUFZLENBQUMsR0FBRyxHQUFHLEdBQUcsQ0FBQztZQUN2QixZQUFZLENBQUMsSUFBSSxHQUFHLFdBQVcsQ0FBQztZQUNoQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUM7UUFDaEMsQ0FBQztRQUVTLFlBQVk7WUFDbEIsTUFBTSxJQUFJLEdBQUcsSUFBSSxDQUFDO1lBQ2xCLE9BQU87Z0JBQ0gsVUFBVSxDQUFDLEtBQWdCLEVBQUUsUUFBNEI7b0JBQ3JELE9BQU8sSUFBSSxDQUFDLFVBQVUsQ0FBQyxLQUFLLEVBQUUsUUFBUSxDQUFDLENBQUM7Z0JBQzVDLENBQUM7Z0JBQ0QsT0FBTyxDQUFDLElBQVMsRUFBRSxVQUFrQixFQUFFLEtBQWdCO29CQUNuRCxPQUFPLElBQUksQ0FBQyxXQUFXLENBQUMsSUFBSSxFQUFFLFVBQVUsRUFBRSxLQUFLLENBQUMsQ0FBQztnQkFDckQsQ0FBQztnQkFDRCxLQUFLLENBQUMsS0FBZ0IsRUFBRSxVQUFrQixFQUFFLFdBQW1CO29CQUMzRCxPQUFPLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBSyxFQUFFLFVBQVUsRUFBRSxXQUFXLENBQUMsQ0FBQztnQkFDMUQsQ0FBQztnQkFDRCxNQUFNLEVBQUUsSUFBSSxDQUFDLFlBQVksRUFBRTthQUM5QixDQUFDO1FBQ04sQ0FBQztRQUVTLFlBQVk7WUFDbEIsT0FBTyxJQUFJLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsSUFBSSxLQUFLLENBQUM7UUFDM0MsQ0FBQztRQUVTLFVBQVUsQ0FBQyxLQUFnQixFQUFFLFFBQTRCO1FBQ25FLENBQUM7UUFFUyxXQUFXLENBQUMsWUFBaUIsRUFBRSxVQUFrQixFQUFFLEtBQWdCO1lBQ3pFLElBQUksQ0FBQyxxQkFBcUIsRUFBRSxDQUFDO1lBQzdCLElBQUksQ0FBQyxjQUFjLENBQUMsWUFBWSxDQUFDLENBQUM7UUFDdEMsQ0FBQztRQUVTLFNBQVMsQ0FBQyxLQUFnQixFQUFFLFVBQWtCLEVBQUUsV0FBbUI7WUFDekUsSUFBSSxDQUFDLHFCQUFxQixFQUFFLENBQUM7WUFFN0IsS0FBSyxDQUFDLFlBQVksQ0FBQyxDQUFDO1FBQ3hCLENBQUM7UUFFUyxRQUFRO1lBRWQsTUFBTSxJQUFJLEdBQWtDLEVBQUUsQ0FBQztZQUMvQyxJQUFJLENBQUMsR0FBRyxFQUFFLENBQUMsSUFBSSxDQUFDLENBQUMsS0FBSyxFQUFFLElBQUksRUFBRSxFQUFFO2dCQUM1QixNQUFNLElBQUksR0FBRyxJQUFJLENBQUMsWUFBWSxDQUFDLE1BQU0sQ0FBQyxDQUFDO2dCQUN2QyxJQUFJLENBQUMsSUFBSSxFQUFFO29CQUNQLE9BQU87aUJBQ1Y7Z0JBQ0QsSUFBSSxDQUFDLElBQUksQ0FBQztvQkFDTixJQUFJO29CQUNKLEtBQUssRUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztpQkFDL0IsQ0FBQyxDQUFDO1lBQ1AsQ0FBQyxDQUFDLENBQUM7WUFDSCxPQUFPLElBQUksQ0FBQztRQUNoQixDQUFDO1FBRVMsR0FBRztZQUNULE9BQU8sSUFBSSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQVUsTUFBTyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUM7UUFDakUsQ0FBQztRQUVTLHFCQUFxQjtZQUMzQixJQUFJLENBQUMsZUFBZSxFQUFFLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxLQUFLLENBQUMsQ0FBQztRQUNuRCxDQUFDO1FBRVMsc0JBQXNCO1lBQzVCLElBQUksQ0FBQyxlQUFlLEVBQUUsQ0FBQyxJQUFJLENBQUMsVUFBVSxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ2xELENBQUM7UUFFUyxlQUFlO1lBQ3JCLE9BQU8sSUFBSSxDQUFDLEdBQUcsRUFBRSxDQUFDLE1BQU0sQ0FBQztnQkFDckIsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBRSxDQUFDLFNBQVMsQ0FBQyxDQUFDO1lBQ2pDLENBQUMsQ0FBQyxDQUFDO1FBQ1AsQ0FBQztRQUVTLGNBQWMsQ0FBQyxNQUFrQjtZQUN2QyxJQUFJLE1BQU0sQ0FBQyxHQUFHLEVBQUU7Z0JBQ1osSUFBSSxDQUFDLGlCQUFpQixDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsQ0FBQzthQUN0QztpQkFBTSxJQUFJLE1BQU0sQ0FBQyxFQUFFLEVBQUU7Z0JBQ2xCLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUM7YUFDcEM7aUJBQU07Z0JBQ0gsSUFBSSxDQUFDLG9CQUFvQixFQUFFLENBQUM7YUFDL0I7UUFDTCxDQUFDO1FBRVMsZ0JBQWdCLENBQUMsWUFBaUI7WUFDeEMsSUFBSSxZQUFZLENBQUMsUUFBUSxFQUFFO2dCQUN2QixpQkFBVSxDQUFDLFlBQVksQ0FBQyxRQUFRLENBQUMsQ0FBQztnQkFDbEMsT0FBTyxJQUFJLENBQUM7YUFDZjtRQUNMLENBQUM7UUFFUyxpQkFBaUIsQ0FBQyxZQUEyQjtZQUNuRCxJQUFJLEtBQUssQ0FBQyxPQUFPLENBQUMsWUFBWSxDQUFDLEVBQUU7Z0JBQzdCLE1BQU0sTUFBTSxHQUFHLFlBQVksQ0FBQyxHQUFHLENBQUMsQ0FBQyxPQUE2QixFQUFFLEVBQUU7b0JBQzlELE9BQU8sSUFBSSxzQkFBWSxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUUsT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDO2dCQUN4RCxDQUFDLENBQUMsQ0FBQztnQkFDSCxJQUFJLENBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxDQUFDO2FBQzNCO2lCQUFNO2dCQUNILElBQUksQ0FBQyxvQkFBb0IsRUFBRSxDQUFDO2FBQy9CO1FBQ0wsQ0FBQztRQUVTLG9CQUFvQjtZQUMxQixLQUFLLENBQUMsa0JBQWtCLENBQUMsQ0FBQztRQUM5QixDQUFDO1FBRVMsa0JBQWtCO1lBQ3hCLElBQUksTUFBTSxHQUFHLElBQUksQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLGNBQWMsQ0FBQyxDQUFDO1lBQzFDLElBQUksVUFBVSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsR0FBRyxHQUFHLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDO1lBQ2hFLElBQUksVUFBVSxDQUFDLE1BQU0sRUFBRTtnQkFDbkIsTUFBTSxHQUFHLFVBQVUsQ0FBQzthQUN2QjtpQkFBTTtnQkFDSCxVQUFVLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxHQUFHLEdBQUcsSUFBSSxDQUFDLDRCQUE0QixDQUFDLENBQUM7Z0JBQ3JFLElBQUksVUFBVSxDQUFDLE1BQU0sRUFBRTtvQkFDbkIsTUFBTSxHQUFHLFVBQVUsQ0FBQztpQkFDdkI7YUFDSjtZQUNELElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFFO2dCQUNoQixPQUFPO2FBQ1Y7UUFFTCxDQUFDOztJQXhVTCxvQkF5VUM7SUF4VTBCLDJCQUFzQixHQUFXLFNBQVMsQ0FBQzs7Ozs7O0lDekR0RSxTQUFnQixFQUFFLENBQUMsT0FBZTtRQUU5QixPQUFPLE9BQU8sQ0FBQztJQUNuQixDQUFDO0lBSEQsZ0JBR0M7O0FDSEQsQ0FBQyxHQUFHLEVBQUU7SUFDRixJQUFJLE1BQU0sR0FBVyxDQUFDLENBQUM7SUFDdkIsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLEdBQUcsVUFBd0IsRUFBaUM7UUFDakUsSUFBSSxRQUFRLEdBQVcsTUFBTSxDQUFDLE1BQU0sRUFBRSxDQUFDLEdBQUcsWUFBWSxDQUFDO1FBQ3ZELE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFHLEdBQUcsUUFBUSxDQUFDO2FBQzFCLFFBQVEsQ0FBQyxRQUFRLENBQUM7YUFDbEIsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDO0lBQ2xCLENBQUMsQ0FBQztBQUNOLENBQUMsQ0FBQyxFQUFFLENBQUM7QUFFTCxDQUFDLENBQUMsZUFBZSxHQUFHLFVBQVUsS0FBVyxFQUFFLEdBQUcsSUFBVztJQUNyRCxPQUFPLENBQUMsQ0FBQyxRQUFRLEVBQUUsQ0FBQyxPQUFPLENBQUMsS0FBSyxFQUFFLEdBQUcsSUFBSSxDQUFDLENBQUMsT0FBTyxFQUFFLENBQUM7QUFDMUQsQ0FBQyxDQUFDO0FBRUYsQ0FBQyxDQUFDLGVBQWUsR0FBRyxVQUFVLEtBQVcsRUFBRSxHQUFHLElBQVc7SUFDckQsT0FBTyxDQUFDLENBQUMsUUFBUSxFQUFFLENBQUMsTUFBTSxDQUFDLEtBQUssRUFBRSxHQUFHLElBQUksQ0FBQyxDQUFDLE9BQU8sRUFBRSxDQUFDO0FBQ3pELENBQUMsQ0FBQyJ9