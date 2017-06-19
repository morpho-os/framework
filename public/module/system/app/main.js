define("system/app/main", ["require", "exports", "../lib/message"], function (require, exports, message_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    $(function () {
        message_1.initPageMessenger();
    });
});
