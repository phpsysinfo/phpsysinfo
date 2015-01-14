function renderPlugin_psstatus(data) {

    var directives = {
        Status: {
            text: function () {
                return (this['Status'] === "1") ? "ON" : "OFF";
            }
        }
    };

    if (data['Plugins']['Plugin_PSStatus'] !== undefined) {
        var psitems = items(data['Plugins']['Plugin_PSStatus']['Process']);
        if (psitems.length > 0) {
            var ps_memory = [];
            ps_memory.push_attrs(psitems);
            $('#psstatus-data').render(ps_memory, directives);
            $('#psstatus_Name').removeClass("sorttable_sorted"); // reset sort order
            sorttable.innerSortFunction.apply($('#psstatus_Name')[0], []);

            $('#block_psstatus').show();
        } else {
            $('#block_psstatus').hide();
        }
    } else {
        $('#block_psstatus').hide();
    }
}
