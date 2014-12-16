function renderPlugin_UpdateNotifier(data) {

    var directives = {
        updateNotifierNbPackages: {
            text: function () {
                return this['packages'];
            }
        },
        updateNotifierNbSecPackages: {
            text: function () {
                return this['security'];
            }
        }
    };
    if ((data !== undefined) && (data["UpdateNotifier"] !== undefined)){
        $('#updatenotifier').render(data["UpdateNotifier"], directives);
        $('#block_updatenotifier').show();
    }
}
