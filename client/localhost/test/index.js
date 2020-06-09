define("localhost/test/index", ["require", "exports", "localhost/lib/test/jasmine"], function (require, exports, jasmine_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.main = void 0;
    const env = jasmine_1.bootJasmine();
    function main() {
        const container = $('#main__body');
        const stackTraceFormatter = (stack) => {
            return Promise.resolve(stack);
        };
        env.addReporter(new jasmine_1.TestResultsReporter(container, stackTraceFormatter));
        const seleniumReporter = {
            jasmineDone(runDetails) {
                if (window.location.search.indexOf('bot') >= 0) {
                    document.getElementById('main__body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
                }
            }
        };
        env.addReporter(seleniumReporter);
        env.execute();
    }
    exports.main = main;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJpbmRleC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7O0lBS0EsTUFBTSxHQUFHLEdBQUcscUJBQVcsRUFBRSxDQUFDO0lBTTFCLFNBQWdCLElBQUk7UUFDaEIsTUFBTSxTQUFTLEdBQUcsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDO1FBU25DLE1BQU0sbUJBQW1CLEdBQUcsQ0FBQyxLQUFhLEVBQW1CLEVBQUU7WUFDM0QsT0FBTyxPQUFPLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxDQUFDO1FBQ2xDLENBQUMsQ0FBQztRQUVGLEdBQUcsQ0FBQyxXQUFXLENBQUMsSUFBSSw2QkFBbUIsQ0FBQyxTQUFTLEVBQUUsbUJBQW1CLENBQUMsQ0FBQyxDQUFDO1FBRXpFLE1BQU0sZ0JBQWdCLEdBQUc7WUFDckIsV0FBVyxDQUFDLFVBQThCO2dCQUN0QyxJQUFJLE1BQU0sQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLEVBQUU7b0JBQzlCLFFBQVEsQ0FBQyxjQUFjLENBQUMsWUFBWSxDQUFFLENBQUMsU0FBUyxJQUFJLDJCQUEyQixHQUFHLFVBQVUsQ0FBQyxrQkFBa0IsQ0FBQyxNQUFNLEdBQUcsT0FBTyxDQUFDO2lCQUNsSjtZQUNMLENBQUM7U0FDSixDQUFDO1FBQ0YsR0FBRyxDQUFDLFdBQVcsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDO1FBSWxDLEdBQUcsQ0FBQyxPQUFPLEVBQUUsQ0FBQztJQUNsQixDQUFDO0lBNUJELG9CQTRCQyJ9