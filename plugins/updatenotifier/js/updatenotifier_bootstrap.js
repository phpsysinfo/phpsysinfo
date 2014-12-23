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
    if ((data['Plugins']['Plugin_UpdateNotifier'] !== undefined) && (data['Plugins']['Plugin_UpdateNotifier']["UpdateNotifier"] !== undefined)){
        $('#updatenotifier').render(data['Plugins']['Plugin_UpdateNotifier']["UpdateNotifier"], directives);
        $('#block_updatenotifier').show();
    }
}
