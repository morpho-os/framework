export abstract class TestCase {
    public setUp(): void {
    }

    public tearDown(): void {
    }

    protected assertEquals(expected: any, actual: any): void {
        if (!eq(expected, actual, [], [])) {
            throw new Error(this.valueToString(expected) + ' !== ' + this.valueToString(actual));
        }
    }

    protected assertTrue(actual: any): void {
        if (actual !== true) {
            throw new Error(this.valueToString(actual) + ' !== true');
        }
    }

    protected assertFalse(actual: any): void {
        if (actual !== false) {
            throw new Error(this.valueToString(actual) + ' !== false');
        }
    }

    protected valueToString(value: any): string {
        if (value === undefined) {
            return 'undefined';
        }
        if (Array.isArray(value)) {
            return '[' + value.toString() + ']';
        }
        // @TODO
        return value.toString();
    }

    protected fail(message?: string): void {
        throw new Error(message ? message : "Test has failed");
    }
}


// The eq() adapted from the https://github.com/jasmine/jasmine/blob/master/src/core/matchers/matchersUtil.js, Jasmine Testing framework.
function eq(a: any, b: any, aStack: any[], bStack: any[]): boolean {
    let result = true;

    if (a instanceof Error && b instanceof Error) {
        return a.message == b.message;
    }

    // Identical objects are equal. `0 === -0`, but they aren't identical.
    // See the [Harmony `egal` proposal](http://wiki.ecmascript.org/doku.php?id=harmony:egal).
    if (a === b) {
        return a !== 0 || 1 / a == 1 / b;
    }

    // A strict comparison is necessary because `null == undefined`.
    if (a === null || b === null) {
        return a === b;
    }

    const className = Object.prototype.toString.call(a);
    if (className != Object.prototype.toString.call(b)) {
        return false;
    }
    switch (className) {
        // Strings, numbers, dates, and booleans are compared by value.
        case '[object String]':
            // Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
            // equivalent to `new String("5")`.
            return a == String(b);
        case '[object Number]':
            // `NaN`s are equivalent, but non-reflexive. An `egal` comparison is performed for
            // other numeric values.
            return a != +a ? b != +b : (a === 0 ? 1 / a == 1 / b : a == +b);
        case '[object Date]':
        case '[object Boolean]':
            // Coerce dates and booleans to numeric primitive values. Dates are compared by their
            // millisecond representations. Note that invalid dates with millisecond representations
            // of `NaN` are not equivalent.
            return +a == +b;
        // RegExps are compared by their source patterns and flags.
        case '[object RegExp]':
            return a.source == b.source &&
                a.global == b.global &&
                a.multiline == b.multiline &&
                a.ignoreCase == b.ignoreCase;
    }
    if (typeof a != 'object' || typeof b != 'object') {
        return false;
    }

    const aIsDomNode = isDomNode(a);
    const bIsDomNode = isDomNode(b);
    if (aIsDomNode && bIsDomNode) {
        // At first try to use DOM3 method isEqualNode
        if (a.isEqualNode) {
            return a.isEqualNode(b);
        }
        // IE8 doesn't support isEqualNode, try to use outerHTML && innerText
        const aIsElement = a instanceof Element;
        const bIsElement = b instanceof Element;
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

    // Assume equality for cyclic structures. The algorithm for detecting cyclic
    // structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.
    let length = aStack.length;
    while (length--) {
        // Linear search. Performance is inversely proportional to the number of
        // unique nested structures.
        if (aStack[length] == a) {
            return bStack[length] == b;
        }
    }
    // Add the first object to the stack of traversed objects.
    aStack.push(a);
    bStack.push(b);
    let size = 0;
    // Recursively compare objects and arrays.
    // Compare array lengths to determine if a deep comparison is necessary.
    if (className == '[object Array]' && a.length !== b.length) {
        result = false;
    }

    if (result) {
        // Objects with different constructors are not equivalent, but `Object`s
        // or `Array`s from different frames are.
        if (className !== '[object Array]') {
            const aCtor = a.constructor, bCtor = b.constructor;
            if (aCtor !== bCtor && !(isFunction(aCtor) && aCtor instanceof aCtor &&
                isFunction(bCtor) && bCtor instanceof bCtor)) {
                return false;
            }
        }
        // Deep compare objects.
        for (let key in a) {
            if (has(a, key)) {
                // Count the expected number of properties.
                size++;
                // Deep compare each member.
                if (!(result = has(b, key) && eq(a[key], b[key], aStack, bStack))) {
                    break;
                }
            }
        }
        // Ensure that both objects contain the same number of properties.
        if (result) {
            for (let key in b) {
                if (has(b, key) && !(size--)) {
                    break;
                }
            }
            result = !size;
        }
    }
    // Remove the first object from the stack of traversed objects.
    aStack.pop();
    bStack.pop();

    return result;

    function has(obj: any, key: any): boolean {
        return Object.prototype.hasOwnProperty.call(obj, key);
    }

    function isFunction(obj: any): boolean {
        return typeof obj === 'function';
    }

    // Taken from the j$.isDomNode().
    function isDomNode(obj: any): boolean {
        return obj.nodeType > 0;
    }
}
