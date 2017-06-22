/// <reference path="../lib/bom.d.ts" />

describe("Extension of the BOM/Browser Object Model", function() {
    describe('Math float', function () {
        it("Math.roundFloat", () => expect(Math.floatsEqual(Math.roundFloat(Math.PI, 2), 3.14)).toBeTruthy());

        it("Math.floatLessThanZero", () => expect(Math.floatLessThanZero(-0.0001)).toBeTruthy());
        it("Math.floatLessThanZero", () => expect(Math.floatLessThanZero(0)).toBeFalsy());
        it("Math.floatLessThanZero", () => expect(Math.floatLessThanZero(0.0001)).toBeFalsy());

        it("Math.isFloatGreaterThanZero", () => expect(Math.floatGreaterThanZero(0.0001)).toBeTruthy());
        it("Math.isFloatGreaterThanZero", () => expect(Math.floatGreaterThanZero(0)).toBeFalsy());
        it("Math.isFloatGreaterThanZero", () => expect(Math.floatGreaterThanZero(-0.0001)).toBeFalsy());

        it("Math.isFloatEqualZero", () => expect(Math.floatEqualZero(0)).toBeTruthy());
        it("Math.isFloatEqualZero", () => expect(Math.floatEqualZero(0.0001)).toBeFalsy());
        it("Math.isFloatEqualZero", () => expect(Math.floatEqualZero(-0.0001)).toBeFalsy());

        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(0, 0)).toBeTruthy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(Math.PI, Math.PI)).toBeTruthy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(Math.PI, -Math.PI)).toBeFalsy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(-Math.PI, -Math.PI)).toBeTruthy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(-Math.PI, Math.PI)).toBeFalsy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(0, -0.0001)).toBeFalsy());
        it("Math.isFloatsEqual", () => expect(Math.floatsEqual(0, 0.0001)).toBeFalsy());
    });
});