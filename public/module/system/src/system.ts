/// <reference path="bom" />
/// <reference path="jquery-ext" />
/// <reference path="widget" />

namespace System {
    export class CommonRegExp {
        public static EMAIL = /^[^@]+@[^@]+$/;
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

    export function loadScript(src: string): void {
        let node = document.createElement('script');
        node.type = 'text/javascript';
        node.charset = 'utf-8';
        //node.async = true;
        document.getElementsByTagName('head')[0].appendChild(node);
    }

    export function loadStyle() {

    }

    export class Uri {
        public prependWithBasePath(uri: string): string {
            // @TODO
            return uri;
        }
    }
    export var uri = new Uri();
}