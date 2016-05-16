/// <reference path="bom.d.ts" />
/// <reference path="jquery-ext.d.ts" />
/// <reference path="widget.d.ts" />

declare namespace Morpho.System {
    class CommonRegExp {
        static EMAIL: RegExp;
    }
    function tr(message: string): string;
    function showUnknownError(message?: string): void;
    function redirectToSelf(): void;
    function redirectToHome(): void;
    function redirectTo(uri: string): void;
    class Uri {
        prependWithBasePath(uri: string): string;
    }
    var uri: Uri;
}