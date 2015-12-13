/// <reference path="d.ts/stacktrace.d.ts" />
/// <reference path="d.ts/bom.d.ts" />
var System;
(function (System) {
    function defineFunctionNameProp() {
        var fnNamePrefixRegex = /^[\S\s]*?function\s*/;
        var fnNameSuffixRegex = /[\s\(\/][\S\s]+$/;
        Object.defineProperty(Function.prototype, "name", {
            get: function () {
                var name = "";
                if (this === Function || this === Function.prototype.constructor) {
                    name = "Function";
                }
                else if (this !== Function.prototype) {
                    name = ("" + this).replace(fnNamePrefixRegex, "").replace(fnNameSuffixRegex, "");
                }
                return name;
            }
        });
    }
    var TestCase = (function () {
        function TestCase() {
            this.setUp();
            this.run();
        }
        TestCase.prototype.setUp = function () {
        };
        TestCase.prototype.assertEquals = function (expected, actual) {
            if (!eq(expected, actual, [], [])) {
                throw new Error(this.valueToString(expected) + ' !== ' + this.valueToString(actual));
            }
        };
        TestCase.prototype.assertTrue = function (actual) {
            if (actual !== true) {
                throw new Error(this.valueToString(actual) + ' !== true');
            }
        };
        TestCase.prototype.assertFalse = function (actual) {
            if (actual !== false) {
                throw new Error(this.valueToString(actual) + ' !== false');
            }
        };
        TestCase.prototype.run = function () {
            this.runTests();
        };
        TestCase.prototype.runTests = function () {
            var _this = this;
            this.getTests().forEach(function (fn) {
                _this.runTest.call(_this, fn);
            });
        };
        TestCase.prototype.runTestInIsolatedEnv = function (test) {
            test.call(this);
        };
        TestCase.prototype.runTest = function (test) {
            try {
                this.runTestInIsolatedEnv(test);
                function functionName(fn) {
                    return fn.displayName || fn.name || (/function ([^\(]+)?\(/.exec(fn.toString()) || [])[1] || null;
                }
                function showSuccess(message) {
                    document.body.innerHTML += '<div style="background: green; color: white; padding: .5em; border-radius: 5px;">' + message + '</div>';
                }
                showSuccess(functionName(test) + '() passed');
            }
            catch (e) {
                var found = false;
                function showError(error, trace) {
                    var message = error.message.escapeHtml();
                    if (!message.length) {
                        message = '&mdash;';
                    }
                    document.body.innerHTML += '<div style="background: red; color: white; padding: .5em; border-radius: 5px;">Failed</div><div>Error message: ' + message + "</div><pre>Stack trace:\n" + trace + '</pre>';
                }
                StackTrace.fromError(e)
                    .then(function (frames) {
                    var trace = frames.filter(function (frame) {
                        return frame;
                    }).map(function (frame) {
                        if (!found) {
                            var functionName = frame.functionName.split('.').pop();
                            if (functionName.substr(0, 4) == 'test' && functionName.length > 4) {
                                found = true;
                                return '<div style="color: red">' + frame.toString().escapeHtml() + '</div>';
                            }
                        }
                        return frame.toString().escapeHtml();
                    }).join('\n');
                    showError(e, trace);
                }, function (reason) {
                    showError(e, reason);
                });
            }
        };
        TestCase.prototype.getTests = function () {
            var isTest = /^test[a-zA-Z_$]+/;
            var fns = [];
            for (var prop in this) {
                if (isTest.test(prop) && typeof this[prop] === 'function') {
                    fns.push(this[prop]);
                }
            }
            return fns;
        };
        TestCase.prototype.valueToString = function (value) {
            if (value === undefined) {
                return 'undefined';
            }
            if (Array.isArray(value)) {
                return '[' + value.toString() + ']';
            }
            return value.toString();
        };
        TestCase.prototype.fail = function (message) {
            throw new Error(message ? message : "Test has failed");
        };
        return TestCase;
    })();
    System.TestCase = TestCase;
    function eq(a, b, aStack, bStack) {
        var result = true;
        if (a instanceof Error && b instanceof Error) {
            return a.message == b.message;
        }
        if (a === b) {
            return a !== 0 || 1 / a == 1 / b;
        }
        if (a === null || b === null) {
            return a === b;
        }
        var className = Object.prototype.toString.call(a);
        if (className != Object.prototype.toString.call(b)) {
            return false;
        }
        switch (className) {
            case '[object String]':
                return a == String(b);
            case '[object Number]':
                return a != +a ? b != +b : (a === 0 ? 1 / a == 1 / b : a == +b);
            case '[object Date]':
            case '[object Boolean]':
                return +a == +b;
            case '[object RegExp]':
                return a.source == b.source &&
                    a.global == b.global &&
                    a.multiline == b.multiline &&
                    a.ignoreCase == b.ignoreCase;
        }
        if (typeof a != 'object' || typeof b != 'object') {
            return false;
        }
        var aIsDomNode = isDomNode(a);
        var bIsDomNode = isDomNode(b);
        if (aIsDomNode && bIsDomNode) {
            if (a.isEqualNode) {
                return a.isEqualNode(b);
            }
            var aIsElement = a instanceof Element;
            var bIsElement = b instanceof Element;
            if (aIsElement && bIsElement) {
                return a.outerHTML == b.outerHTML;
            }
            if (aIsElement || bIsElement) {
                return false;
            }
            return a.innerText == b.innerText && a.textContent == b.textContent;
        }
        if (aIsDomNode || bIsDomNode) {
            return false;
        }
        var length = aStack.length;
        while (length--) {
            if (aStack[length] == a) {
                return bStack[length] == b;
            }
        }
        aStack.push(a);
        bStack.push(b);
        var size = 0;
        if (className == '[object Array]' && a.length !== b.length) {
            result = false;
        }
        if (result) {
            if (className !== '[object Array]') {
                var aCtor = a.constructor, bCtor = b.constructor;
                if (aCtor !== bCtor && !(isFunction(aCtor) && aCtor instanceof aCtor &&
                    isFunction(bCtor) && bCtor instanceof bCtor)) {
                    return false;
                }
            }
            for (var key in a) {
                if (has(a, key)) {
                    size++;
                    if (!(result = has(b, key) && eq(a[key], b[key], aStack, bStack))) {
                        break;
                    }
                }
            }
            if (result) {
                for (key in b) {
                    if (has(b, key) && !(size--)) {
                        break;
                    }
                }
                result = !size;
            }
        }
        aStack.pop();
        bStack.pop();
        return result;
        function has(obj, key) {
            return Object.prototype.hasOwnProperty.call(obj, key);
        }
        function isFunction(obj) {
            return typeof obj === 'function';
        }
        function isDomNode(obj) {
            return obj.nodeType > 0;
        }
    }
})(System || (System = {}));
