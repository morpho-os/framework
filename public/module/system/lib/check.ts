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
                /*            const chunk = line.split('@').pop();
            return chunk.substring(0, chunk.indexOf(':', 5)); */
            });
        });
    };
    //env.addReporter(jasmineInterface.jsApiReporter);
    env.addReporter(new TestResultsReporter($container, stackTraceFormatter));

    const seleniumReporter = {
        jasmineDone(runDetails: jasmine.RunDetails) { // @TODO: Specify more concrete type
            if (window.location.search.indexOf('selenium') >= 0) {
                document.getElementById('page-body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
            }
        }
    };
    env.addReporter(seleniumReporter);
    //jasmine.getEnv().throwOnExpectationFailure(true);

    env.execute();
}
