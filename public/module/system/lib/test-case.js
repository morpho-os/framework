define("system/test/../lib/test-case", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var TestCase = (function () {
        function TestCase() {
        }
        TestCase.prototype.setUp = function () {
        };
        TestCase.prototype.tearDown = function () {
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
    }());
    exports.TestCase = TestCase;
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
                for (var key in b) {
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
});
