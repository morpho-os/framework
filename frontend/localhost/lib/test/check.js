define("localhost/lib/test/check", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.checkTrue = exports.checkFalse = exports.checkLength = exports.checkNoEl = exports.checkEmpty = exports.checkEqual = void 0;
    function checkEqual(expected, actual) {
        expect(actual).toEqual(expected);
    }
    exports.checkEqual = checkEqual;
    function checkEmpty(arr) {
        checkLength(0, arr);
    }
    exports.checkEmpty = checkEmpty;
    function checkNoEl($el) {
        checkLength(0, $el);
    }
    exports.checkNoEl = checkNoEl;
    function checkLength(expectedLength, list) {
        checkEqual(expectedLength, list.length);
    }
    exports.checkLength = checkLength;
    function checkFalse(actual) {
        expect(actual).toBeFalsy();
    }
    exports.checkFalse = checkFalse;
    function checkTrue(actual) {
        expect(actual).toBeTruthy();
    }
    exports.checkTrue = checkTrue;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY2hlY2suanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJjaGVjay50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7O0lBT0EsU0FBZ0IsVUFBVSxDQUFDLFFBQWEsRUFBRSxNQUFXO1FBQ2pELE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLENBQUM7SUFDckMsQ0FBQztJQUZELGdDQUVDO0lBRUQsU0FBZ0IsVUFBVSxDQUFDLEdBQVU7UUFDakMsV0FBVyxDQUFDLENBQUMsRUFBRSxHQUFHLENBQUMsQ0FBQztJQUN4QixDQUFDO0lBRkQsZ0NBRUM7SUFFRCxTQUFnQixTQUFTLENBQUMsR0FBVztRQUNqQyxXQUFXLENBQUMsQ0FBQyxFQUFFLEdBQUcsQ0FBQyxDQUFDO0lBQ3hCLENBQUM7SUFGRCw4QkFFQztJQUVELFNBQWdCLFdBQVcsQ0FBQyxjQUFzQixFQUFFLElBQW9CO1FBQ3BFLFVBQVUsQ0FBQyxjQUFjLEVBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDO0lBQzVDLENBQUM7SUFGRCxrQ0FFQztJQUVELFNBQWdCLFVBQVUsQ0FBQyxNQUFXO1FBQ2xDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQyxTQUFTLEVBQUUsQ0FBQztJQUMvQixDQUFDO0lBRkQsZ0NBRUM7SUFFRCxTQUFnQixTQUFTLENBQUMsTUFBVztRQUNqQyxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUMsVUFBVSxFQUFFLENBQUM7SUFDaEMsQ0FBQztJQUZELDhCQUVDIn0=