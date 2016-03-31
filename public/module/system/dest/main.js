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
Math.isFloatsEqual = function (a, b) {
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
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var System;
(function (System) {
    var Exception = (function (_super) {
        __extends(Exception, _super);
        function Exception(message) {
            _super.call(this, message);
            this.message = message;
            this.name = 'Exception';
            this.message = message;
        }
        Exception.prototype.toString = function () {
            return this.name + ': ' + this.message;
        };
        return Exception;
    }(Error));
    System.Exception = Exception;
    var NotImplementedException = (function (_super) {
        __extends(NotImplementedException, _super);
        function NotImplementedException() {
            _super.apply(this, arguments);
        }
        return NotImplementedException;
    }(Exception));
    System.NotImplementedException = NotImplementedException;
})(System || (System = {}));
var System;
(function (System) {
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
    System.EventManager = EventManager;
})(System || (System = {}));
var System;
(function (System) {
    var Widget = (function (_super) {
        __extends(Widget, _super);
        function Widget(el) {
            _super.call(this);
            this.el = $(el);
            this.init();
            this.registerEventHandlers();
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
    }(System.EventManager));
    System.Widget = Widget;
    var ProgressBar = (function (_super) {
        __extends(ProgressBar, _super);
        function ProgressBar() {
            _super.apply(this, arguments);
        }
        return ProgressBar;
    }(Widget));
    var Menu = (function (_super) {
        __extends(Menu, _super);
        function Menu() {
            _super.apply(this, arguments);
        }
        return Menu;
    }(Widget));
    var Window = (function (_super) {
        __extends(Window, _super);
        function Window() {
            _super.apply(this, arguments);
        }
        return Window;
    }(Widget));
    System.Window = Window;
    var ModalWindow = (function (_super) {
        __extends(ModalWindow, _super);
        function ModalWindow() {
            _super.apply(this, arguments);
        }
        return ModalWindow;
    }(Window));
})(System || (System = {}));
var System;
(function (System) {
    var CommonRegExp = (function () {
        function CommonRegExp() {
        }
        CommonRegExp.EMAIL = /^[^@]+@[^@]+$/;
        return CommonRegExp;
    }());
    System.CommonRegExp = CommonRegExp;
    function tr(message) {
        return message;
    }
    System.tr = tr;
    function showUnknownError(message) {
        alert("Unknown error, please contact support");
    }
    System.showUnknownError = showUnknownError;
    function redirectToSelf() {
        redirectTo(window.location.href);
    }
    System.redirectToSelf = redirectToSelf;
    function redirectToHome() {
        redirectTo(System.uri.prependWithBasePath('/'));
    }
    System.redirectToHome = redirectToHome;
    function redirectTo(uri) {
        window.location.href = uri;
    }
    System.redirectTo = redirectTo;
    var Uri = (function () {
        function Uri() {
        }
        Uri.prototype.prependWithBasePath = function (uri) {
            return uri;
        };
        return Uri;
    }());
    System.Uri = Uri;
    System.uri = new Uri();
})(System || (System = {}));
var System;
(function (System) {
    var ResourceLoader = (function () {
        function ResourceLoader() {
        }
        ResourceLoader.loadStyle = function (uri) {
        };
        ResourceLoader.loadScript = function (uri) {
            var node = document.createElement('script');
            node.type = 'text/javascript';
            node.charset = 'utf-8';
            document.getElementsByTagName('head')[0].appendChild(node);
        };
        ResourceLoader.loadImage = function (uri) {
        };
        return ResourceLoader;
    }());
})(System || (System = {}));
var System;
(function (System) {
    var MessageManager = (function (_super) {
        __extends(MessageManager, _super);
        function MessageManager() {
            _super.apply(this, arguments);
        }
        MessageManager.prototype.getNumberOfMessages = function () {
            return this.getMessageEls().length;
        };
        MessageManager.prototype.getMessageEls = function () {
            return this.el.find('.alert');
        };
        return MessageManager;
    }(System.Widget));
    System.MessageManager = MessageManager;
    var PageMessageManager = (function (_super) {
        __extends(PageMessageManager, _super);
        function PageMessageManager() {
            _super.apply(this, arguments);
        }
        PageMessageManager.prototype.registerEventHandlers = function () {
            _super.prototype.registerEventHandlers.call(this);
            this.registerCloseMessageHandler();
        };
        PageMessageManager.prototype.registerCloseMessageHandler = function () {
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
                if (self.getNumberOfMessages() === 1) {
                    hideMainContainerWithAnim();
                }
                else {
                    var $messageContainer = $message.closest('.messages');
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
        };
        return PageMessageManager;
    }(MessageManager));
    System.PageMessageManager = PageMessageManager;
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
    System.Message = Message;
})(System || (System = {}));
var System;
(function (System) {
    var Form = (function (_super) {
        __extends(Form, _super);
        function Form() {
            _super.apply(this, arguments);
            this._wasValidated = false;
            this._isValid = null;
            this.messages = {};
        }
        Form.prototype.wasAtLeastOnceValidated = function () {
            return this._wasValidated;
        };
        Form.prototype.validate = function () {
            this._isValid = this._validate();
            this._wasValidated = true;
            return this._isValid;
        };
        Form.prototype.isValid = function () {
            if (!this.wasAtLeastOnceValidated()) {
                throw new Error("Unable to check state, the form should be validated first");
            }
            return this._isValid;
        };
        Form.prototype.getEls = function () {
            return $(this.el[0].elements);
        };
        Form.prototype.getElsToValidate = function () {
            return this.getEls().filter(function () {
                var $el = $(this);
                return $el.is(':not(input[type=submit])') && $el.is(':not(button)');
            });
        };
        Form.prototype.getSubmitButtonEls = function () {
            return this.getEls().filter(function () {
                return $(this).is(':submit');
            });
        };
        ;
        Form.prototype.getInvalidEls = function () {
            if (!this.wasAtLeastOnceValidated()) {
                return $();
            }
            return this.getEls().filter(function () {
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
        Form.prototype.getFormMessages = function (type) {
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
            this.forEach(this.getFormErrorMessages, this.showAddedFormMessage);
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
            this.showFormMessage(new System.Message(1, text));
        };
        Form.prototype.getFormErrorMessages = function () {
            return this.getFormMessages(1);
        };
        Form.prototype.addFormErrorMessage = function (text) {
            this.addFormMessage(new System.Message(1, text));
        };
        Form.prototype.init = function () {
            _super.prototype.init.call(this);
            this.el.attr('novalidate', 'novalidate');
        };
        Form.prototype._showAddedFormMessage = function (message) {
            this.showEl(this.getMessageContainerEl()
                .append(this.formatFormMessage(message)));
        };
        Form.prototype.removeElsErrors = function () {
            this.forEachEl(this.getElsToValidate(), this.removeElErrors);
        };
        Form.prototype.removeElErrors = function ($el) {
            $el.closest('.form-group')
                .removeClass('has-error')
                .find('.error').remove();
            $el.removeClass('invalid');
        };
        Form.prototype.removeFormErrors = function () {
            var $messageContainer = this.getMessageContainerEl();
            $messageContainer.find('.alert-error').remove();
            if ($messageContainer.is(':empty')) {
                this.hideEl($messageContainer);
            }
        };
        Form.prototype.getMessageContainerEl = function () {
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
                throw new System.NotImplementedException("formatMessage");
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
            this.forEachEl(this.getElsToValidate(), function ($el) {
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
            this.showElMessage($el, new System.Message(1, System.tr('This field is required')));
        };
        Form.prototype.showElMessage = function ($el, message) {
            $el.after(this.formatElMessage(message));
            $el.closest('.form-group').addClass('has-error');
        };
        Form.prototype.showElError = function ($el, text) {
            this.showElMessage($el, new System.Message(1, text));
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
            this.sendFormData(this.getUri(), this.getFormData());
        };
        Form.prototype.getUri = function () {
            return this.el.attr('action') || window.location.href;
        };
        Form.prototype.getFormData = function () {
            var _this = this;
            var data = [];
            this.getEls().each(function (index, node) {
                var name = node.getAttribute('name');
                if (!name) {
                    return;
                }
                data.push({
                    name: name,
                    value: _this.getElValue($(node))
                });
            });
            return data;
        };
        Form.prototype.getElValue = function ($el) {
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
            this.getSubmitButtonEls().prop('disabled', false);
        };
        Form.prototype.disableSubmitButtonEls = function () {
            this.getSubmitButtonEls().prop('disabled', true);
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
                System.redirectTo(responseData.redirect);
            }
        };
        Form.prototype.handleResponseError = function (responseData) {
            this.showFormErrorMessage(responseData);
        };
        Form.prototype.changeEventNames = function () {
            return 'keyup blur change paste';
        };
        Form.prototype.showUnknownError = function () {
            System.showUnknownError(null);
        };
        return Form;
    }(System.Widget));
    System.Form = Form;
})(System || (System = {}));
var System;
(function (System) {
    var Application = (function () {
        function Application() {
        }
        Application.main = function () {
            window.pageMessenger = new System.PageMessageManager('#page-messages');
        };
        return Application;
    }());
    System.Application = Application;
})(System || (System = {}));
$(function () {
    System.Application.main();
});
