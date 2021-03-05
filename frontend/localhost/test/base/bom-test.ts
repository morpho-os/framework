/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

/// <amd-module name="localhost/test/bom-test" />

describe('Extension of the "BOM/Browser Object Model"', function() {
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
