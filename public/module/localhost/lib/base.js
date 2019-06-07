define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    function id(value) {
        return value;
    }
    exports.id = id;
    function isPromise(value) {
        return value && $.isFunction(value.promise);
    }
    exports.isPromise = isPromise;
    function isDomNode(obj) {
        return obj.nodeType > 0;
    }
    exports.isDomNode = isDomNode;
    function isGenerator(fn) {
        return fn.constructor.name === 'GeneratorFunction';
    }
    exports.isGenerator = isGenerator;
    class Re {
    }
    Re.email = /^[^@]+@[^@]+$/;
    exports.Re = Re;
    function showUnknownError(message) {
        alert("Unknown error, please contact support");
    }
    exports.showUnknownError = showUnknownError;
    function redirectToSelf() {
        window.location.reload();
    }
    exports.redirectToSelf = redirectToSelf;
    function redirectToHome() {
        redirectTo('/');
    }
    exports.redirectToHome = redirectToHome;
    function redirectTo(uri, storePageInHistory = false) {
        if (storePageInHistory) {
            window.location.href = uri;
        }
        else {
            window.location.replace(uri);
        }
    }
    exports.redirectTo = redirectTo;
    function queryArgs() {
        const decode = (input) => decodeURIComponent(input.replace(/\+/g, ' '));
        const parser = /([^=?&]+)=?([^&]*)/g;
        let queryArgs = {}, part;
        while (part = parser.exec(window.location.search)) {
            let key = decode(part[1]), value = decode(part[2]);
            if (key in queryArgs) {
                continue;
            }
            queryArgs[key] = value;
        }
        return queryArgs;
    }
    exports.queryArgs = queryArgs;
    function delayedCallback(callback, waitMs) {
        let timer = 0;
        return function () {
            const self = this;
            const args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(self, args);
            }, waitMs);
        };
    }
    exports.delayedCallback = delayedCallback;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmFzZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbImJhc2UudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7O0lBS0EsU0FBZ0IsRUFBRSxDQUFDLEtBQVU7UUFDekIsT0FBTyxLQUFLLENBQUM7SUFDakIsQ0FBQztJQUZELGdCQUVDO0lBRUQsU0FBZ0IsU0FBUyxDQUFDLEtBQVU7UUFDaEMsT0FBTyxLQUFLLElBQUksQ0FBQyxDQUFDLFVBQVUsQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUFDLENBQUM7SUFDaEQsQ0FBQztJQUZELDhCQUVDO0lBR0QsU0FBZ0IsU0FBUyxDQUFDLEdBQVE7UUFDOUIsT0FBTyxHQUFHLENBQUMsUUFBUSxHQUFHLENBQUMsQ0FBQztJQUM1QixDQUFDO0lBRkQsOEJBRUM7SUFFRCxTQUFnQixXQUFXLENBQUMsRUFBWTtRQUNwQyxPQUFhLEVBQUUsQ0FBQyxXQUFZLENBQUMsSUFBSSxLQUFLLG1CQUFtQixDQUFDO0lBQzlELENBQUM7SUFGRCxrQ0FFQztJQUVELE1BQWEsRUFBRTs7SUFDWSxRQUFLLEdBQUcsZUFBZSxDQUFDO0lBRG5ELGdCQUVDO0lBS0QsU0FBZ0IsZ0JBQWdCLENBQUMsT0FBZ0I7UUFFN0MsS0FBSyxDQUFDLHVDQUF1QyxDQUFDLENBQUM7SUFDbkQsQ0FBQztJQUhELDRDQUdDO0lBRUQsU0FBZ0IsY0FBYztRQUUxQixNQUFNLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBRSxDQUFDO0lBQzdCLENBQUM7SUFIRCx3Q0FHQztJQUVELFNBQWdCLGNBQWM7UUFHMUIsVUFBVSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQ3BCLENBQUM7SUFKRCx3Q0FJQztJQUVELFNBQWdCLFVBQVUsQ0FBQyxHQUFXLEVBQUUsa0JBQWtCLEdBQUcsS0FBSztRQUM5RCxJQUFJLGtCQUFrQixFQUFFO1lBQ3BCLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxHQUFHLEdBQUcsQ0FBQztTQUM5QjthQUFNO1lBQ0gsTUFBTSxDQUFDLFFBQVEsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUM7U0FDaEM7SUFDTCxDQUFDO0lBTkQsZ0NBTUM7SUFHRCxTQUFnQixTQUFTO1FBQ3JCLE1BQU0sTUFBTSxHQUFHLENBQUMsS0FBYSxFQUFVLEVBQUUsQ0FBQyxrQkFBa0IsQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUFDLEtBQUssRUFBRSxHQUFHLENBQUMsQ0FBQyxDQUFDO1FBRXhGLE1BQU0sTUFBTSxHQUFHLHFCQUFxQixDQUFDO1FBQ3JDLElBQUksU0FBUyxHQUF1QixFQUFFLEVBQ2xDLElBQUksQ0FBQztRQUVULE9BQU8sSUFBSSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsRUFBRTtZQUMvQyxJQUFJLEdBQUcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQ3JCLEtBQUssR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFLNUIsSUFBSSxHQUFHLElBQUksU0FBUyxFQUFFO2dCQUNsQixTQUFTO2FBQ1o7WUFDRCxTQUFTLENBQUMsR0FBRyxDQUFDLEdBQUcsS0FBSyxDQUFDO1NBQzFCO1FBRUQsT0FBTyxTQUFTLENBQUM7SUFDckIsQ0FBQztJQXJCRCw4QkFxQkM7SUFJRCxTQUFnQixlQUFlLENBQUMsUUFBa0IsRUFBRSxNQUFjO1FBQzlELElBQUksS0FBSyxHQUFHLENBQUMsQ0FBQztRQUNkLE9BQU87WUFDSCxNQUFNLElBQUksR0FBRyxJQUFJLENBQUM7WUFDbEIsTUFBTSxJQUFJLEdBQUcsU0FBUyxDQUFDO1lBQ3ZCLFlBQVksQ0FBQyxLQUFLLENBQUMsQ0FBQztZQUNwQixLQUFLLEdBQUcsVUFBVSxDQUFDO2dCQUNmLFFBQVEsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO1lBQy9CLENBQUMsRUFBRSxNQUFNLENBQUMsQ0FBQztRQUNmLENBQUMsQ0FBQztJQUNOLENBQUM7SUFWRCwwQ0FVQyJ9