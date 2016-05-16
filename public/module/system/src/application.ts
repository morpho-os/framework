/// <reference path="message"/>

namespace Morpho.System {
    export class Application {
        public static main() {
            (<any>window).pageMessenger = new PageMessageManager('#page-messages');
        }
    }
}