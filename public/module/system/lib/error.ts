namespace Morpho.System {
    export class Exception extends Error {
        //public stack: any;

        constructor(public message: string) {
            super(message);
            this.name = 'Exception';
            this.message = message;
            //        this.stack = (<any>new Error()).stack;
        }

        toString() {
            return this.name + ': ' + this.message;
        }
    }

    export class NotImplementedException extends Exception {
    }
}
