function renderPlugin_iptables(data) {

    var directives = {
        Rule: {
            html: function () {
                return this.Rule;
            }
        }
    };

    if ((data.Plugins.Plugin_iptables !== undefined) && (data.Plugins.Plugin_iptables.iptables !== undefined)) {
        var upitems = items(data.Plugins.Plugin_iptables.iptables.Item);
        if (upitems.length > 0) {
            var up_memory = [];
            up_memory.push_attrs(upitems);
            $('#iptables-data').render(up_memory, directives);

            $('#block_iptables').show();
        } else {
            $('#block_iptables').hide();
        }
    } else {
        $('#block_iptables').hide();
    }
}
