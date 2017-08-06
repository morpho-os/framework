import {bootJasmine} from "./jasmine";

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
const {htmlReporter, env} = bootJasmine();

export function main() {
    jasmine.getEnv().addReporter({
        jasmineDone(runDetails) {
            if (window.location.search.indexOf('selenium') >= 0) {
                document.getElementById('page-body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
                //alert($('.jasmine_html-reporter .jasmine-alert .jasmine-passed').text())
            }
        }
    });

    htmlReporter.initialize();
    env.execute();
}
