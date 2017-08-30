/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
import {bootJasmine, TestResultsReporter} from "./jasmine";

export function checkEqual(expected: any, actual: any): void {
    expect(actual).toEqual(expected);
}

export function checkEmpty(arr: any[]): void {
    checkLength(0, arr);
}

export function checkNoEl($el: JQuery): void {
    checkLength(0, $el);
}

export function checkLength(expectedLength: number, list: any[] | JQuery) {
    checkEqual(expectedLength, list.length);
}

export function checkFalse(actual: any) {
    expect(actual).toBeFalsy();
}

export function checkTrue(actual: any) {
    expect(actual).toBeTruthy();
}

// We need to boot jasmine on the module inclusion, so that the global 'jasmine' object would be accessible for tests.
const env = bootJasmine();

interface SourceMappedStackTrace {
    mapStackTrace(stack: string, done: (mappedStack: string[]) => void): void;
}

export function main($container: JQuery, sourceMappedStackTrace: SourceMappedStackTrace): void {
    const stackTraceFormatter = (stack: string): Promise<string> => {
        return new Promise(function (resolve) {
            sourceMappedStackTrace.mapStackTrace(stack, (mappedStack: string[]) => {
                resolve(mappedStack.join("\n"));
            });
        });
    };
    //env.addReporter(jasmineInterface.jsApiReporter);
    env.addReporter(new TestResultsReporter($container, stackTraceFormatter));

    const seleniumReporter = {
        jasmineDone(runDetails: jasmine.RunDetails) {
            if (window.location.search.indexOf('selenium') >= 0) {
                document.getElementById('main__body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
            }
        }
    };
    env.addReporter(seleniumReporter);

    //jasmine.getEnv().throwOnExpectationFailure(true);

    env.execute();
}
