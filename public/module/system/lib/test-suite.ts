import {TestCase} from "./test-case";

export class TestSuite {
    public run(tests: () => TestCase[]): void {
        alert(tests().length);
        //new Childform($());
    }
}

/*function defineFunctionNameProp() {
    // https://github.com/kryptnostic/Function.name/blob/master/Function.name.js
    const fnNamePrefixRegex = /^[\S\s]*?function\s*!/;
    const fnNameSuffixRegex = /[\s\(\/][\S\s]+$/;
    Object.defineProperty(Function.prototype, "name", {
        get: function () {
            let name = "";
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


protected runTestInIsolatedEnv(test: (() => void)): void {
    test.call(this);
}

protected runTest(test: (() => void)): void {
    function functionName(fn: any) {
    /*
    if (fn.name) {
        return fn.name;
    }
    var ret = fn.toString();
    ret = ret.substr('function '.length);
    ret = ret.substr(0, ret.indexOf('('));
    return ret;
    * /
    // https://github.com/sindresorhus/fn-name/blob/master/index.js
    return fn.displayName || fn.name || (/function ([^\(]+)?\(/.exec(fn.toString()) || [])[1] || null;
}

function showSuccess(message: string) {
    document.body.innerHTML += '<div style="background: green; color: white; padding: .5em; border-radius: 5px;">' + message + '</div>';
}

function showError(error: Error): void {
    let trace = '';
    /*
    var found = false;
    StackTrace.fromError(e)
        .then(function (frames: any) {
            var trace = frames.filter(function (frame: any) {
                // @TODO: Filter?
                return frame;
            }).map(function (frame: any) {
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
    * /

    let message = error.message.escapeHtml();
    if (!message.length) {
        message = '&mdash;'
    }
    document.body.innerHTML += '<div style="background: red; color: white; padding: .5em; border-radius: 5px;">Failed</div><div>Error message: ' + message + "</div><pre>Stack trace:\n" + trace + '</pre>';
}

try {
    this.runTestInIsolatedEnv(test);
    showSuccess(functionName(test)  + '() passed');
} catch (e) {
    showError(e);
}
}

protected getTests(): (() => void)[] {
    const isTest = /^test[a-zA-Z_$]+/;
    const fns: (() => void)[] = [];
    for (const prop in this) {
        if (isTest.test(prop) && typeof this[prop] === 'function') {
            fns.push(this[prop]);
        }
    }
    return fns;
}


protected run(): void {
    this.runTests();
}

protected runTests(): void {
    this.getTests().forEach((fn) => {
    this.runTest.call(this, fn);
});
}

protected runTestInIsolatedEnv(test: (() => void)): void {
    test.call(this);
}

protected runTest(test: (() => void)): void {
    function functionName(fn: any) {
    /*
    if (fn.name) {
        return fn.name;
    }
    var ret = fn.toString();
    ret = ret.substr('function '.length);
    ret = ret.substr(0, ret.indexOf('('));
    return ret;
    * /
    // https://github.com/sindresorhus/fn-name/blob/master/index.js
    return fn.displayName || fn.name || (/function ([^\(]+)?\(/.exec(fn.toString()) || [])[1] || null;
}

function showSuccess(message: string) {
    document.body.innerHTML += '<div style="background: green; color: white; padding: .5em; border-radius: 5px;">' + message + '</div>';
}

function showError(error: Error): void {
    let trace = '';
    /*
    var found = false;
    StackTrace.fromError(e)
        .then(function (frames: any) {
            var trace = frames.filter(function (frame: any) {
                // @TODO: Filter?
                return frame;
            }).map(function (frame: any) {
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
    * /

    let message = error.message.escapeHtml();
    if (!message.length) {
        message = '&mdash;'
    }
    document.body.innerHTML += '<div style="background: red; color: white; padding: .5em; border-radius: 5px;">Failed</div><div>Error message: ' + message + "</div><pre>Stack trace:\n" + trace + '</pre>';
}

try {
    this.runTestInIsolatedEnv(test);
    showSuccess(functionName(test)  + '() passed');
} catch (e) {
    showError(e);
}
}

protected getTests(): (() => void)[] {
    const isTest = /^test[a-zA-Z_$]+/;
    const fns: (() => void)[] = [];
    for (const prop in this) {
        if (isTest.test(prop) && typeof this[prop] === 'function') {
            fns.push(this[prop]);
        }
    }
    return fns;
}
*/