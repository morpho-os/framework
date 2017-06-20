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
Math.EPS = 0.000001;
Math.roundFloat = function (val, precision) {
    if (precision === void 0) { precision = 2; }
    var dd = Math.pow(10, precision);
    return Math.round(val * dd) / dd;
};
Math.isFloatLessThanZero = function (val) {
    return val < -Math.EPS;
};
Math.isFloatGreaterThanZero = function (val) {
    return val > Math.EPS;
};
Math.isFloatEqualZero = function (val) {
    return Math.abs(val) <= Math.EPS;
};
Math.isFloatEqual = function (a, b) {
    return Math.isFloatEqualZero(a - b);
};
String.prototype.escapeHtml = function () {
    var entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
    };
    return this.replace(/[&<>"'\/]/g, function (s) {
        return entityMap[s];
    });
};
String.prototype.titleize = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
};
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
                handlers[i].apply(handlers, args);
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
        Widget.prototype.showEl = function ($el) {
            $el.removeClass('hide').show();
        };
        Widget.prototype.hideEl = function ($el) {
            $el.hide();
        };
        Widget.prototype.forEach = function (items, fn) {
            var _this = this;
            $.each(items, function (key, value) {
                fn.call(_this, value);
            });
        };
        Widget.prototype.forEachEl = function (items, fn) {
            var _this = this;
            $.each(items, function (key, value) {
                fn.call(_this, $(value));
            });
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
define("system/lib/message", ["require", "exports", "system/lib/widget"], function (require, exports, widget_1) {
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
    function messageTypeToString(messageType) {
        switch (messageType) {
            case 8:
                return 'debug';
            case 4:
                return 'info';
            case 2:
                return 'warning';
            case 1:
                return 'error';
            default:
                throw new Error("Invalid message type");
        }
    }
    var Message = (function () {
        function Message(type, text) {
            this.type = type;
            this.text = text;
        }
        Message.prototype.typeToString = function () {
            return messageTypeToString(this.type);
        };
        Message.prototype.hasType = function (type) {
            return this.type === type;
        };
        return Message;
    }());
    exports.Message = Message;
});
define("system/lib/system", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var Re = (function () {
        function Re() {
        }
        return Re;
    }());
    Re.EMAIL = /^[^@]+@[^@]+$/;
    exports.Re = Re;
    function tr(message) {
        return message;
    }
    exports.tr = tr;
    function showUnknownError(message) {
        alert("Unknown error, please contact support");
    }
    exports.showUnknownError = showUnknownError;
    function redirectToSelf() {
        redirectTo(window.location.href);
    }
    exports.redirectToSelf = redirectToSelf;
    function redirectToHome() {
        redirectTo(exports.uri.prependWithBasePath('/'));
    }
    exports.redirectToHome = redirectToHome;
    function redirectTo(uri) {
        window.location.href = uri;
    }
    exports.redirectTo = redirectTo;
    var Uri = (function () {
        function Uri() {
        }
        Uri.prototype.prependWithBasePath = function (uri) {
            return uri;
        };
        return Uri;
    }());
    exports.Uri = Uri;
    exports.uri = new Uri();
    function isGenerator(fn) {
        return fn.constructor.name === 'GeneratorFunction';
    }
    exports.isGenerator = isGenerator;
});
define("system/lib/form", ["require", "exports", "system/lib/message", "system/lib/error", "system/lib/system", "system/lib/widget"], function (require, exports, message_1, error_1, system_1, widget_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var Form = (function (_super) {
        __extends(Form, _super);
        function Form() {
            var _this = _super !== null && _super.apply(this, arguments) || this;
            _this._wasValidated = false;
            _this._isValid = null;
            _this.messages = {};
            return _this;
        }
        Form.prototype.wasValidated = function () {
            return this._wasValidated;
        };
        Form.prototype.validate = function () {
            this._isValid = this._validate();
            this._wasValidated = true;
            return this._isValid;
        };
        Form.prototype.isValid = function () {
            if (!this.wasValidated()) {
                throw new Error("Unable to check state, the form should be validated first");
            }
            return this._isValid;
        };
        Form.prototype.els = function () {
            return $(this.el[0].elements);
        };
        Form.prototype.elsToValidate = function () {
            return this.els().filter(function () {
                var $el = $(this);
                return $el.is(':not(input[type=submit])') && $el.is(':not(button)');
            });
        };
        Form.prototype.submitButtonEls = function () {
            return this.els().filter(function () {
                return $(this).is(':submit');
            });
        };
        ;
        Form.prototype.invalidEls = function () {
            if (!this.wasValidated()) {
                return $();
            }
            return this.els().filter(function () {
                return $(this).hasClass('invalid');
            });
        };
        Form.prototype.addFormMessage = function (message) {
            var type = message.type;
            if (typeof this.messages[type] === 'undefined') {
                this.messages[type] = [];
            }
            this.messages[type].push(message);
        };
        Form.prototype.formMessages = function (type) {
            var _this = this;
            if (type === void 0) { type = null; }
            var messages = [], concatMessages = function (type) {
                if (typeof _this.messages[type] !== 'undefined') {
                    messages = messages.concat(_this.messages[type]);
                }
            };
            if (null === type) {
                type = 15;
            }
            if (type & 8) {
                concatMessages(8);
            }
            if (type & 4) {
                concatMessages(4);
            }
            if (type & 2) {
                concatMessages(2);
            }
            if (type & 1) {
                concatMessages(1);
            }
            return messages;
        };
        Form.prototype.showFormMessage = function (message) {
            this.addFormMessage(message);
            this._showAddedFormMessage(message);
        };
        Form.prototype.showAddedFormMessages = function () {
            this.forEach(this.formErrorMessages, this.showAddedFormMessage);
        };
        Form.prototype.showAddedFormMessage = function (message) {
            this.ensureIsAddedFormMessage(message);
        };
        Form.prototype.showFormMessages = function (messages) {
            this.forEach(messages, this.showFormMessage);
        };
        Form.prototype.hasErrors = function () {
            return !this.isValid();
        };
        Form.prototype.clearErrors = function () {
            this.removeElsErrors();
            this.removeFormErrors();
            this.messages[1] = [];
        };
        Form.prototype.showFormErrorMessage = function (text) {
            this.showFormMessage(new message_1.Message(1, text));
        };
        Form.prototype.formErrorMessages = function () {
            return this.formMessages(1);
        };
        Form.prototype.addFormErrorMessage = function (text) {
            this.addFormMessage(new message_1.Message(1, text));
        };
        Form.prototype.init = function () {
            _super.prototype.init.call(this);
            this.el.attr('novalidate', 'novalidate');
        };
        Form.prototype._showAddedFormMessage = function (message) {
            this.showEl(this.messageContainerEl()
                .append(this.formatFormMessage(message)));
        };
        Form.prototype.removeElsErrors = function () {
            this.forEachEl(this.elsToValidate(), this.removeElErrors);
        };
        Form.prototype.removeElErrors = function ($el) {
            $el.closest('.form-group')
                .removeClass('has-error')
                .find('.error').remove();
            $el.removeClass('invalid');
        };
        Form.prototype.removeFormErrors = function () {
            var $messageContainer = this.messageContainerEl();
            $messageContainer.find('.alert-error').remove();
            if ($messageContainer.is(':empty')) {
                this.hideEl($messageContainer);
            }
        };
        Form.prototype.messageContainerEl = function () {
            var containerCssClass = 'messages', $containerEl = this.el.find('.' + containerCssClass);
            if (!$containerEl.length) {
                $containerEl = $('<div class="' + containerCssClass + '"></div>').prependTo(this.el);
            }
            return $containerEl;
        };
        Form.prototype.ensureIsAddedFormMessage = function (message) {
            if (!this.isAddedFormMessage(message)) {
                throw new Error("Message must be added first");
            }
        };
        Form.prototype.isAddedFormMessage = function (message) {
            return $.inArray(message, this.messages[message.type]) >= 0;
        };
        Form.prototype.formatFormMessage = function (message) {
            if (!message.hasType(1)) {
                throw new error_1.NotImplementedException("formatMessage");
            }
            return '<div class="alert alert-error">' + message.text + '</div>';
        };
        Form.prototype._validate = function () {
            this.clearErrors();
            return this.validateEls();
        };
        Form.prototype.validateEls = function () {
            var _this = this;
            var isValid = true;
            this.forEachEl(this.elsToValidate(), function ($el) {
                if (!_this.validateEl($el)) {
                    isValid = false;
                }
            });
            return isValid;
        };
        Form.prototype.validateEl = function ($el) {
            this.removeElErrors($el);
            if (this.isRequiredEl($el)) {
                return this.validateRequiredEl($el);
            }
            return true;
        };
        Form.prototype.validateRequiredEl = function ($el) {
            var val = $el.val().trim();
            if (!val.length) {
                $el.addClass('invalid');
                this.showValueRequiredElError($el);
                return false;
            }
            return true;
        };
        Form.prototype.showValueRequiredElError = function ($el) {
            this.showElMessage($el, new message_1.Message(1, system_1.tr('Это поле обязательно для заполнения.')));
        };
        Form.prototype.showElMessage = function ($el, message) {
            $el.after(this.formatElMessage(message));
            $el.closest('.form-group').addClass('has-error');
        };
        Form.prototype.showElError = function ($el, text) {
            this.showElMessage($el, new message_1.Message(1, text));
        };
        Form.prototype.formatElMessage = function (message) {
            return '<div class="' + message.typeToString() + '">' + message.text + '</div>';
        };
        Form.prototype.isRequiredEl = function ($el) {
            return $el.is('[required]');
        };
        Form.prototype.registerEventHandlers = function () {
            this.registerSubmitEventHandler();
        };
        Form.prototype.registerSubmitEventHandler = function () {
            this.el.on('submit', this.handleSubmit.bind(this));
        };
        Form.prototype.handleSubmit = function () {
            this.clearErrors();
            if (this.validate()) {
                this.submit();
            }
            return false;
        };
        Form.prototype.submit = function () {
            this.disableSubmitButtonEls();
            this.sendFormData(this.uri(), this.formData());
        };
        Form.prototype.uri = function () {
            return this.el.attr('action') || window.location.href;
        };
        Form.prototype.formData = function () {
            var _this = this;
            var data = [];
            this.els().each(function (index, node) {
                var name = node.getAttribute('name');
                if (!name) {
                    return;
                }
                data.push({
                    name: name,
                    value: _this.elValue($(node))
                });
            });
            return data;
        };
        Form.prototype.elValue = function ($el) {
            if ($el.get(0)['type'] == 'checkbox') {
                return $el.is(':checked') ? 1 : 0;
            }
            return $el.val();
        };
        Form.prototype.sendFormData = function (uri, requestData) {
            var ajaxSettings = this.ajaxSettings();
            ajaxSettings.url = uri;
            ajaxSettings.data = requestData;
            return this.sendAjaxRequest(ajaxSettings);
        };
        Form.prototype.sendAjaxRequest = function (ajaxSettings) {
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
        Form.prototype.enableSubmitButtonEls = function () {
            this.submitButtonEls().prop('disabled', false);
        };
        Form.prototype.disableSubmitButtonEls = function () {
            this.submitButtonEls().prop('disabled', true);
        };
        Form.prototype.handleResponse = function (responseData) {
            if (responseData.error) {
                this.handleResponseError(responseData.error);
            }
            else if (responseData.success) {
                this.handleResponseSuccess(responseData.success);
            }
            else {
                this.showUnknownError();
            }
        };
        Form.prototype.handleResponseSuccess = function (responseData) {
            if (responseData.redirect) {
                system_1.redirectTo(responseData.redirect);
                return true;
            }
        };
        Form.prototype.handleResponseError = function (responseData) {
            this.showFormErrorMessage(responseData);
        };
        Form.prototype.changeEventNames = function () {
            return 'keyup blur change paste';
        };
        Form.prototype.showUnknownError = function () {
            system_1.showUnknownError(null);
        };
        return Form;
    }(widget_2.Widget));
    exports.Form = Form;
});
var uniqId = 0;
$.fn.once = function (fn) {
    var cssClass = String(uniqId++) + '-processed';
    return this.not('.' + cssClass)
        .addClass(cssClass)
        .each(fn);
};
$.resolvedPromise = function (value) {
    var args = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        args[_i - 1] = arguments[_i];
    }
    return (_a = $.Deferred()).resolve.apply(_a, [value].concat(args)).promise();
    var _a;
};
$.isPromise = function (value) {
    return value && $.isFunction(value.promise);
};
$.isDomNode = function (obj) {
    return obj.nodeType > 0;
};
