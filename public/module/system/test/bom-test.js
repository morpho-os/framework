/*'use strict';

/*
 'toBe',
 'toBeCloseTo',
 'toBeDefined',
 'toBeFalsy',
 'toBeGreaterThan',
 'toBeLessThan',
 'toBeNaN',
 'toBeNull',
 'toBeTruthy',
 'toBeUndefined',
 'toContain',
 'toEqual',
 'toHaveBeenCalled',
 'toHaveBeenCalledWith',
 'toMatch',
 'toThrow',
 'toThrowError'
 ],

describe("Morpho", function () {
    it("Math.roundFloat", () => expect(Math.isFloatsEqual(Math.roundFloat(Math.PI, 2), 3.14)).toBeTruthy());

    it("Math.isFloatLessThanZero", () => expect(Math.isFloatLessThanZero(-0.0001)).toBeTruthy());
    it("Math.isFloatLessThanZero", () => expect(Math.isFloatLessThanZero(0)).toBeFalsy());
    it("Math.isFloatLessThanZero", () => expect(Math.isFloatLessThanZero(0.0001)).toBeFalsy());

    it("Math.isFloatGreaterThanZero", () => expect(Math.isFloatGreaterThanZero(0.0001)).toBeTruthy());
    it("Math.isFloatGreaterThanZero", () => expect(Math.isFloatGreaterThanZero(0)).toBeFalsy());
    it("Math.isFloatGreaterThanZero", () => expect(Math.isFloatGreaterThanZero(-0.0001)).toBeFalsy());

    it("Math.isFloatEqualZero", () => expect(Math.isFloatEqualZero(0)).toBeTruthy());
    it("Math.isFloatEqualZero", () => expect(Math.isFloatEqualZero(0.0001)).toBeFalsy());
    it("Math.isFloatEqualZero", () => expect(Math.isFloatEqualZero(-0.0001)).toBeFalsy());

    it("Math.isFloatsEqual", () => expect(Math.isFloatsEqual(0, 0)).toBeTruthy());
    it("Math.isFloatsEqual", () => expect(Math.isFloatsEqual(Math.PI, Math.PI)).toBeTruthy());
    it("Math.isFloatsEqual", () => expect(Math.isFloatsEqual(Math.PI, -Math.PI)).toBeFalsy());
    it("Math.isFloatsEqual", () => expect(Math.isFloatsEqual(-Math.PI, -Math.PI)).toBeTruthy());
    it("Math.isFloatsEqual", () => expect(Math.isFloatsEqual(-Math.PI, Math.PI)).toBeFalsy());
    it("Math.isFloatsEqual", () => expect(Math.isFloatsEqual(0, -0.0001)).toBeFalsy());
    it("Math.isFloatsEqual", () => expect(Math.isFloatsEqual(0, 0.0001)).toBeFalsy());

    describe('OOP', function () {
        it("Inheritance", function () {
            function Parent() {

            }

            Parent.prototype.foo = function () {
                return "Parent.foo()";
            };

            function Child() {

            }

            Child.inherits(Parent);
            Child.prototype.foo = function () {
                return "Child.foo().before " + this._parent.foo() + ' Child.foo().after';
            };

            var child = new Child();
            expect(child.foo()).toEqual("Child.foo().before Parent.foo() Child.foo().after");

            expect(child instanceof Parent).toBeTruthy();
        });

        it("Calling parent constructor from child constructor", function () {
            function Parent() {
                this._parentCtorCalled = Array.prototype.slice.call(arguments, 0);
            }

            function Child() {
                this._parent.__construct.apply(this, ["Call", "from", "child", "!"]);
            }
            Child.inherits(Parent);
            var child = new Child();

            expect(child._parentCtorCalled).toEqual(['Call', 'from', 'child', '!']);
        });
    });

    describe('Widgets', function () {
        it("Form", function () {
            expect(new Form() instanceof Widget).toBeTruthy();
        });
    });
});

*/ 
