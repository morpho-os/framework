/// <reference path="stacktrace.d.ts" />
/// <reference path="bom.d.ts" />

declare namespace Morpho.System {
    abstract class TestCase {
        constructor();
        protected setUp(): void;
        protected assertEquals(expected: any, actual: any): void;
        protected assertTrue(actual: any): void;
        protected assertFalse(actual: any): void;
        protected run(): void;
        protected runTests(): void;
        protected runTestInIsolatedEnv(test: (() => void)): void;
        protected runTest(test: (() => void)): void;
        protected getTests(): (() => void)[];
        protected valueToString(value: any): string;
        protected fail(message?: string): void;
    }
}