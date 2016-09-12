declare interface Math {
    EPS: number;
    roundFloat(val: number, precision: number): number;
    isFloatLessThanZero(val: number): boolean;
    isFloatGreaterThanZero(val: number): boolean;
    isFloatEqualZero(val: number): boolean;
    isFloatsEqual(a: number, b: number): boolean;
}
declare interface String {
    escapeHtml(): string;
    titleize(): string;
}