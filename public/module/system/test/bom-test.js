"use strict";
describe('Extension of the "BOM/Browser Object Model"', function () {
    describe('Math float', function () {
        it("Math.roundFloat", function () { return expect(Math.floatsEqual(Math.roundFloat(Math.PI, 2), 3.14)).toBeTruthy(); });
        it("Math.floatLessThanZero", function () { return expect(Math.floatLessThanZero(-0.0001)).toBeTruthy(); });
        it("Math.floatLessThanZero", function () { return expect(Math.floatLessThanZero(0)).toBeFalsy(); });
        it("Math.floatLessThanZero", function () { return expect(Math.floatLessThanZero(0.0001)).toBeFalsy(); });
        it("Math.isFloatGreaterThanZero", function () { return expect(Math.floatGreaterThanZero(0.0001)).toBeTruthy(); });
        it("Math.isFloatGreaterThanZero", function () { return expect(Math.floatGreaterThanZero(0)).toBeFalsy(); });
        it("Math.isFloatGreaterThanZero", function () { return expect(Math.floatGreaterThanZero(-0.0001)).toBeFalsy(); });
        it("Math.isFloatEqualZero", function () { return expect(Math.floatEqualZero(0)).toBeTruthy(); });
        it("Math.isFloatEqualZero", function () { return expect(Math.floatEqualZero(0.0001)).toBeFalsy(); });
        it("Math.isFloatEqualZero", function () { return expect(Math.floatEqualZero(-0.0001)).toBeFalsy(); });
        it("Math.isFloatsEqual", function () { return expect(Math.floatsEqual(0, 0)).toBeTruthy(); });
        it("Math.isFloatsEqual", function () { return expect(Math.floatsEqual(Math.PI, Math.PI)).toBeTruthy(); });
        it("Math.isFloatsEqual", function () { return expect(Math.floatsEqual(Math.PI, -Math.PI)).toBeFalsy(); });
        it("Math.isFloatsEqual", function () { return expect(Math.floatsEqual(-Math.PI, -Math.PI)).toBeTruthy(); });
        it("Math.isFloatsEqual", function () { return expect(Math.floatsEqual(-Math.PI, Math.PI)).toBeFalsy(); });
        it("Math.isFloatsEqual", function () { return expect(Math.floatsEqual(0, -0.0001)).toBeFalsy(); });
        it("Math.isFloatsEqual", function () { return expect(Math.floatsEqual(0, 0.0001)).toBeFalsy(); });
    });
});
