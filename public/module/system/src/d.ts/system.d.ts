/// <reference path="bom.d.ts" />
/// <reference path="jquery-ext.d.ts" />
/// <reference path="widget.d.ts" />

declare namespace System {
    class CommonRegExp {
        static EMAIL: RegExp;
    }
    function redirectToSelf(): void;
    function redirectToHome(): void;
    function redirectTo(uri: string): void;
    function loadScript(src: string): void;
    function loadStyle(): void;
    class Uri {
        prependWithBasePath(uri: string): string;
    }
    var uri: Uri;
}