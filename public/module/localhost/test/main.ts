/// <amd-module name="localhost/test/main" />

// We need to boot jasmine on the module inclusion, so that the global 'jasmine' object would be accessible for tests.
import {bootJasmine, TestResultsReporter} from "../lib/test/jasmine";

const env = bootJasmine();

/*interface SourceMappedStackTrace {
    mapStackTrace(stack: string, done: (mappedStack: string[]) => void): void;
}*/

export function main(/*, sourceMappedStackTrace: SourceMappedStackTrace*/): void {
    const container = $('#main__body');

    /*const stackTraceFormatter = (stack: string): Promise<string> => {
        return new Promise(function (resolve) {
            sourceMappedStackTrace.mapStackTrace(stack, (mappedStack: string[]) => {
                resolve(mappedStack.join("\n"));
            });
        });
    };*/
    const stackTraceFormatter = (stack: string): Promise<string> => {
        return Promise.resolve(stack);
    };
    //env.addReporter(jasmineInterface.jsApiReporter);
    env.addReporter(new TestResultsReporter(container, stackTraceFormatter));

    const seleniumReporter = {
        jasmineDone(runDetails: jasmine.RunDetails) {
            if (window.location.search.indexOf('bot') >= 0) {
                (<HTMLElement>document.getElementById('main__body')).innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
            }
        }
    };
    env.addReporter(seleniumReporter);

    //jasmine.getEnv().throwOnExpectationFailure(true);

    env.execute();
}
