define("localhost/lib/app/index", ["require", "exports", "localhost/lib/base/app"], function (require, exports, app_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    class App extends app_1.App {
        bindEventHandlers() {
            this.bindMainMenuHandlers();
        }
        bindMainMenuHandlers() {
            const uriPath = window.location.pathname;
            $('#main-menu a').each(function () {
                const $a = $(this);
                let linkUri = $a.attr('href');
                if (!linkUri) {
                    return;
                }
                if (linkUri.substr(0, 1) !== '/') {
                    return;
                }
                let offset = linkUri.indexOf('?');
                if (offset >= 0) {
                    linkUri = linkUri.substr(0, offset);
                }
                offset = linkUri.indexOf('#');
                if (offset >= 0) {
                    linkUri = linkUri.substr(0, offset);
                }
                if (linkUri === uriPath) {
                    $a.addClass('active');
                    $a.closest('.dropdown').find('.nav-link:first').addClass('active');
                }
            });
        }
    }
    window.app = new App();
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJpbmRleC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7SUFJQSxNQUFNLEdBQUksU0FBUSxTQUFPO1FBQ1gsaUJBQWlCO1lBQ3ZCLElBQUksQ0FBQyxvQkFBb0IsRUFBRSxDQUFDO1FBQ2hDLENBQUM7UUFFTyxvQkFBb0I7WUFDeEIsTUFBTSxPQUFPLEdBQUcsTUFBTSxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUM7WUFDekMsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLElBQUksQ0FBQztnQkFDbkIsTUFBTSxFQUFFLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO2dCQUNuQixJQUFJLE9BQU8sR0FBRyxFQUFFLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDO2dCQUM5QixJQUFJLENBQUMsT0FBTyxFQUFFO29CQUNWLE9BQU87aUJBQ1Y7Z0JBQ0QsSUFBSSxPQUFPLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBSyxHQUFHLEVBQUU7b0JBQzlCLE9BQU87aUJBQ1Y7Z0JBQ0QsSUFBSSxNQUFNLEdBQUcsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsQ0FBQztnQkFDbEMsSUFBSSxNQUFNLElBQUksQ0FBQyxFQUFFO29CQUNiLE9BQU8sR0FBRyxPQUFPLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxNQUFNLENBQUMsQ0FBQztpQkFDdkM7Z0JBQ0QsTUFBTSxHQUFHLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUM7Z0JBQzlCLElBQUksTUFBTSxJQUFJLENBQUMsRUFBRTtvQkFDYixPQUFPLEdBQUcsT0FBTyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUUsTUFBTSxDQUFDLENBQUM7aUJBQ3ZDO2dCQUNELElBQUksT0FBTyxLQUFLLE9BQU8sRUFBRTtvQkFDckIsRUFBRSxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUMsQ0FBQTtvQkFDckIsRUFBRSxDQUFDLE9BQU8sQ0FBQyxXQUFXLENBQUMsQ0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxRQUFRLENBQUMsUUFBUSxDQUFDLENBQUM7aUJBQ3RFO1lBQ0wsQ0FBQyxDQUFDLENBQUM7UUFDUCxDQUFDO0tBQ0o7SUFFRCxNQUFNLENBQUMsR0FBRyxHQUFHLElBQUksR0FBRyxFQUFFLENBQUMifQ==