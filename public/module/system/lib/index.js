"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
define("system/lib/base", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    function id(value) {
        return value;
    }
    exports.id = id;
    function filterStringArgs(str, args, filter) {
        args.forEach(function (arg, index) {
            str = str.replace('{' + index + '}', arg);
        });
        return str;
    }
    exports.filterStringArgs = filterStringArgs;
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
    var Re = (function () {
        function Re() {
        }
        Re.email = /^[^@]+@[^@]+$/;
        return Re;
    }());
    exports.Re = Re;
});
define("system/lib/bom", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    Math.EPS = 0.000001;
    Math.roundFloat = function (val, precision) {
        if (precision === void 0) { precision = 2; }
        var dd = Math.pow(10, precision);
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
    String.prototype.escapeHtml = function () {
        var entityMap = {
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
    function showUnknownError(message) {
        alert("Unknown error, please contact support");
    }
    exports.showUnknownError = showUnknownError;
    function redirectToSelf() {
        redirectTo(window.location.href);
    }
    exports.redirectToSelf = redirectToSelf;
    function redirectToHome() {
        redirectTo('/');
    }
    exports.redirectToHome = redirectToHome;
    function redirectTo(uri) {
        window.location.href = uri;
    }
    exports.redirectTo = redirectTo;
});
define("system/lib/error", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var Exception = (function (_super) {
        __extends(Exception, _super);
        function Exception(message) {
            var _this = _super.call(this, message) || this;
            _this.message = message;
            _this.name = 'Exception';
            _this.message = message;
            return _this;
        }
        Exception.prototype.toString = function () {
            return this.name + ': ' + this.message;
        };
        return Exception;
    }(Error));
    exports.Exception = Exception;
    var NotImplementedException = (function (_super) {
        __extends(NotImplementedException, _super);
        function NotImplementedException() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        return NotImplementedException;
    }(Exception));
    exports.NotImplementedException = NotImplementedException;
    var UnexpectedValueException = (function (_super) {
        __extends(UnexpectedValueException, _super);
        function UnexpectedValueException() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        return UnexpectedValueException;
    }(Exception));
    exports.UnexpectedValueException = UnexpectedValueException;
});
define("system/lib/event-manager", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var EventManager = (function () {
        function EventManager() {
            this.eventHandlers = {};
        }
        EventManager.prototype.on = function (eventName, handler) {
            this.eventHandlers[eventName] = this.eventHandlers[eventName] || [];
            this.eventHandlers[eventName].push(handler);
        };
        EventManager.prototype.trigger = function (eventName) {
            var args = [];
            for (var _i = 1; _i < arguments.length; _i++) {
                args[_i - 1] = arguments[_i];
            }
            var handlers = this.eventHandlers[eventName];
            if (!handlers) {
                return;
            }
            for (var i = 0; i < handlers.length; ++i) {
                if (false === handlers[i].apply(handlers, args)) {
                    break;
                }
            }
        };
        return EventManager;
    }());
    exports.EventManager = EventManager;
});
define("system/lib/widget", ["require", "exports", "system/lib/event-manager"], function (require, exports, event_manager_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var Widget = (function (_super) {
        __extends(Widget, _super);
        function Widget(el) {
            var _this = _super.call(this) || this;
            _this.el = $(el);
            _this.init();
            _this.registerEventHandlers();
            return _this;
        }
        Widget.prototype.registerEventHandlers = function () {
        };
        Widget.prototype.init = function () {
        };
        return Widget;
    }(event_manager_1.EventManager));
    exports.Widget = Widget;
    var Window = (function (_super) {
        __extends(Window, _super);
        function Window() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        return Window;
    }(Widget));
    exports.Window = Window;
});
define("system/lib/message", ["require", "exports", "system/lib/widget", "system/lib/base"], function (require, exports, widget_1, base_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var MessageType;
    (function (MessageType) {
        MessageType[MessageType["Error"] = 1] = "Error";
        MessageType[MessageType["Warning"] = 2] = "Warning";
        MessageType[MessageType["Info"] = 4] = "Info";
        MessageType[MessageType["Debug"] = 8] = "Debug";
        MessageType[MessageType["All"] = 15] = "All";
    })(MessageType = exports.MessageType || (exports.MessageType = {}));
    function initPageMessenger() {
        window.pageMessenger = new PageMessenger('#page-messages');
        return window.pageMessenger;
    }
    exports.initPageMessenger = initPageMessenger;
    function pageMessenger() {
        var pageMessenger = window.pageMessenger;
        return pageMessenger ? pageMessenger : initPageMessenger();
    }
    exports.pageMessenger = pageMessenger;
    var PageMessenger = (function (_super) {
        __extends(PageMessenger, _super);
        function PageMessenger() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        PageMessenger.prototype.numberOfMessages = function () {
            return this.messageEls().length;
        };
        PageMessenger.prototype.messageEls = function () {
            return this.el.find('.alert');
        };
        PageMessenger.prototype.registerEventHandlers = function () {
            _super.prototype.registerEventHandlers.call(this);
            this.registerCloseMessageHandler();
        };
        PageMessenger.prototype.registerCloseMessageHandler = function () {
            var self = this;
            function hideElWithAnim($el, fn) {
                $el.fadeOut(fn);
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
                    var $messageContainer_1 = $message.closest('.messages');
                    if ($messageContainer_1.find('.alert').length === 1) {
                        hideElWithAnim($messageContainer_1, function () {
                            $messageContainer_1.remove();
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
        };
        return PageMessenger;
    }(widget_1.Widget));
    exports.PageMessenger = PageMessenger;
    function renderMessage(message) {
        var text = message.text.escapeHtml();
        text = base_1.filterStringArgs(text, message.args, base_1.id);
        return wrapMessage(text, messageTypeToStr(message.type));
    }
    exports.renderMessage = renderMessage;
    function wrapMessage(text, type) {
        return '<div class="' + type.toLowerCase().escapeHtml() + '">' + text + '</div>';
    }
    function messageTypeToStr(type) {
        return MessageType[type];
    }
    exports.messageTypeToStr = messageTypeToStr;
    var Message = (function () {
        function Message(type, text, args) {
            if (args === void 0) { args = []; }
            this.type = type;
            this.text = text;
            this.args = args;
        }
        Message.prototype.hasType = function (type) {
            return this.type === type;
        };
        return Message;
    }());
    exports.Message = Message;
    var ErrorMessage = (function (_super) {
        __extends(ErrorMessage, _super);
        function ErrorMessage(text, args) {
            if (args === void 0) { args = []; }
            return _super.call(this, MessageType.Error, text, args) || this;
        }
        return ErrorMessage;
    }(Message));
    exports.ErrorMessage = ErrorMessage;
    var WarningMessage = (function (_super) {
        __extends(WarningMessage, _super);
        function WarningMessage(text, args) {
            if (args === void 0) { args = []; }
            return _super.call(this, MessageType.Warning, text, args) || this;
        }
        return WarningMessage;
    }(Message));
    exports.WarningMessage = WarningMessage;
    var InfoMessage = (function (_super) {
        __extends(InfoMessage, _super);
        function InfoMessage(text, args) {
            if (args === void 0) { args = []; }
            return _super.call(this, MessageType.Warning, text, args) || this;
        }
        return InfoMessage;
    }(Message));
    exports.InfoMessage = InfoMessage;
    var DebugMessage = (function (_super) {
        __extends(DebugMessage, _super);
        function DebugMessage(text, args) {
            if (args === void 0) { args = []; }
            return _super.call(this, MessageType.Debug, text, args) || this;
        }
        return DebugMessage;
    }(Message));
    exports.DebugMessage = DebugMessage;
});
define("system/lib/form", ["require", "exports", "system/lib/message", "system/lib/bom", "system/lib/widget"], function (require, exports, message_1, bom_1, widget_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var RequiredElValidator = (function () {
        function RequiredElValidator() {
        }
        RequiredElValidator.prototype.validate = function ($el) {
            if (Form.isRequiredEl($el)) {
                if (Form.elValue($el).trim().length < 1) {
                    return [RequiredElValidator.EmptyValueMessage];
                }
            }
            return [];
        };
        RequiredElValidator.EmptyValueMessage = 'This field is required';
        return RequiredElValidator;
    }());
    exports.RequiredElValidator = RequiredElValidator;
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
        var errors = [];
        validators.forEach(function (validator) {
            errors = errors.concat(validator.validate($el));
        });
        return errors;
    }
    exports.validateEl = validateEl;
    var Form = (function (_super) {
        __extends(Form, _super);
        function Form() {
            var _this = _super !== null && _super.apply(this, arguments) || this;
            _this.skipValidation = false;
            _this.elContainerCssClass = 'form-group';
            _this.formMessageContainerCssClass = 'messages';
            _this.invalidCssClass = Form.defaultInvalidCssClass;
            return _this;
        }
        Form.elValue = function ($el) {
            if ($el.get(0)['type'] === 'checkbox') {
                return $el.is(':checked') ? 1 : 0;
            }
            return $el.val();
        };
        Form.isRequiredEl = function ($el) {
            return $el.is('[required]');
        };
        Form.prototype.els = function () {
            return $(this.el[0].elements);
        };
        Form.prototype.elsToValidate = function () {
            return this.els().filter(function () {
                var $el = $(this);
                return $el.is(':not(:submit)');
            });
        };
        Form.prototype.validate = function () {
            this.clearErrors();
            var errors = [];
            this.elsToValidate().each(function () {
                var $el = $(this);
                var elErrors = validateEl($el);
                if (elErrors.length) {
                    errors.push([$el, elErrors.map(function (error) { return new message_1.ErrorMessage(error); })]);
                }
            });
            if (errors.length) {
                this.showErrors(errors);
                return false;
            }
            return true;
        };
        Form.prototype.invalidEls = function () {
            var self = this;
            return this.els().filter(function () {
                return $(this).hasClass(self.invalidCssClass);
            });
        };
        Form.prototype.hasErrors = function () {
            return this.el.hasClass(this.invalidCssClass);
        };
        Form.prototype.clearErrors = function () {
            var _this = this;
            this.invalidEls().each(function (index, el) {
                var $el = $(el);
                var $container = $el.removeClass(_this.invalidCssClass).closest('.' + _this.elContainerCssClass);
                if (!$container.find('.' + _this.invalidCssClass).length) {
                    $container.removeClass(_this.invalidCssClass);
                }
                $el.next('.error').remove();
            });
            this.formMessageContainerEl().remove();
            this.el.removeClass(this.invalidCssClass);
        };
        Form.prototype.submit = function () {
            this.clearErrors();
            if (this.skipValidation) {
                this.send();
            }
            else if (this.validate()) {
                this.send();
            }
        };
        Form.prototype.send = function () {
            this.disableSubmitButtonEls();
            return this.sendFormData(this.uri(), this.formData());
        };
        Form.prototype.showErrors = function (errors) {
            var _this = this;
            var formErrors = [];
            errors.forEach(function (err) {
                if (Array.isArray(err)) {
                    var $el = err[0], elErrors = err[1];
                    _this.showElErrors($el, elErrors);
                }
                else {
                    formErrors.push(err);
                }
            });
            this.showFormErrors(formErrors);
            this.scrollToFirstError();
        };
        Form.prototype.showFormErrors = function (errors) {
            var rendered = '<div class="alert alert-error">' + errors.map(message_1.renderMessage).join("\n") + '</div>';
            this.formMessageContainerEl()
                .prepend(rendered);
            this.el.addClass(this.invalidCssClass);
        };
        Form.prototype.showElErrors = function ($el, errors) {
            var invalidCssClass = this.invalidCssClass;
            $el.addClass(invalidCssClass).closest('.' + this.elContainerCssClass).addClass(invalidCssClass);
            $el.after(errors.map(message_1.renderMessage).join("\n"));
        };
        Form.prototype.formMessageContainerEl = function () {
            var containerCssClass = this.formMessageContainerCssClass;
            var $containerEl = this.el.find('.' + containerCssClass);
            if (!$containerEl.length) {
                $containerEl = $('<div class="' + containerCssClass + '"></div>').prependTo(this.el);
            }
            return $containerEl;
        };
        Form.prototype.init = function () {
            _super.prototype.init.call(this);
            this.el.attr('novalidate', 'novalidate');
        };
        Form.prototype.registerEventHandlers = function () {
            var _this = this;
            this.el.on('submit', function () {
                _this.submit();
                return false;
            });
        };
        Form.prototype.sendFormData = function (uri, requestData) {
            var ajaxSettings = this.ajaxSettings();
            ajaxSettings.url = uri;
            ajaxSettings.data = requestData;
            return $.ajax(ajaxSettings);
        };
        Form.prototype.ajaxSettings = function () {
            var self = this;
            return {
                beforeSend: function (jqXHR, settings) {
                    return self.beforeSend(jqXHR, settings);
                },
                success: function (data, textStatus, jqXHR) {
                    return self.ajaxSuccess(data, textStatus, jqXHR);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    return self.ajaxError(jqXHR, textStatus, errorThrown);
                },
                method: this.submitMethod()
            };
        };
        Form.prototype.submitMethod = function () {
            return this.el.attr('method') || 'GET';
        };
        Form.prototype.beforeSend = function (jqXHR, settings) {
        };
        Form.prototype.ajaxSuccess = function (responseData, textStatus, jqXHR) {
            this.enableSubmitButtonEls();
            this.handleResponse(responseData);
        };
        Form.prototype.ajaxError = function (jqXHR, textStatus, errorThrown) {
            this.enableSubmitButtonEls();
            alert("AJAX error");
        };
        Form.prototype.formData = function () {
            var data = [];
            this.els().each(function (index, node) {
                var name = node.getAttribute('name');
                if (!name) {
                    return;
                }
                data.push({
                    name: name,
                    value: Form.elValue($(node))
                });
            });
            return data;
        };
        Form.prototype.uri = function () {
            return this.el.attr('action') || window.location.href;
        };
        Form.prototype.enableSubmitButtonEls = function () {
            this.submitButtonEls().prop('disabled', false);
        };
        Form.prototype.disableSubmitButtonEls = function () {
            this.submitButtonEls().prop('disabled', true);
        };
        Form.prototype.submitButtonEls = function () {
            return this.els().filter(function () {
                return $(this).is(':submit');
            });
        };
        Form.prototype.handleResponse = function (responseData) {
            if (responseData.error) {
                this.handleResponseError(responseData.error);
            }
            else if (responseData.success) {
                this.handleResponseSuccess(responseData.success);
            }
            else {
                this.invalidResponseError();
            }
        };
        Form.prototype.handleResponseSuccess = function (responseData) {
            if (responseData.redirect) {
                bom_1.redirectTo(responseData.redirect);
                return true;
            }
        };
        Form.prototype.handleResponseError = function (responseData) {
            if (Array.isArray(responseData)) {
                var errors = responseData.map(function (message) {
                    return new message_1.ErrorMessage(message.text, message.args);
                });
                this.showErrors(errors);
            }
            else {
                this.invalidResponseError();
            }
        };
        Form.prototype.invalidResponseError = function () {
            alert('Invalid response');
        };
        Form.prototype.scrollToFirstError = function () {
            var $first = this.el.find('.error:first');
            var $container = $first.closest('.' + this.elContainerCssClass);
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
        };
        Form.defaultInvalidCssClass = 'invalid';
        return Form;
    }(widget_2.Widget));
    exports.Form = Form;
});
define("system/lib/i18n", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    function tr(message) {
        return message;
    }
    exports.tr = tr;
});
(function () {
    var uniqId = 0;
    $.fn.once = function (fn) {
        var cssClass = String(uniqId++) + '-processed';
        return this.not('.' + cssClass)
            .addClass(cssClass)
            .each(fn);
    };
})();
$.resolvedPromise = function (value) {
    var args = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        args[_i - 1] = arguments[_i];
    }
    return (_a = $.Deferred()).resolve.apply(_a, [value].concat(args)).promise();
    var _a;
};
$.rejectedPromise = function (value) {
    var args = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        args[_i - 1] = arguments[_i];
    }
    return (_a = $.Deferred()).reject.apply(_a, [value].concat(args)).promise();
    var _a;
};
