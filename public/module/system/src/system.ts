/// <reference path="bom" />
/// <reference path="jquery-ext" />
/// <reference path="widget" />

namespace System {
    export class CommonRegExp {
        public static EMAIL = /^[^@]+@[^@]+$/;
    }

    export function tr(message: string): string {
        // @TODO
        return message;
    }

    export function showUnknownError(message?: string): void {
        // @TODO
        alert("Unknown error, please contact support");
    }

    export function redirectToSelf(): void {
        redirectTo(window.location.href);
    }

    export function redirectToHome(): void {
        redirectTo(uri.prependWithBasePath('/'));
    }

    export function redirectTo(uri: string): void {
        window.location.href = uri;
    }

    export class Uri {
        public prependWithBasePath(uri: string): string {
            // @TODO
            return uri;
        }
    }
    export var uri = new Uri();
}