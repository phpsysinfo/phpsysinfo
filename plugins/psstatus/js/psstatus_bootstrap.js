function renderPlugin_PSStatus(data) {

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
            for (i = 0; i < psitems.length ; i++) {
                ps_memory.push(psitems[i]["@attributes"]);
            }
            $('#psstatus-data').render(ps_memory, directives);
            sorttable.innerSortFunction.apply($('#psstatus_Name')[0], []);

            $('#block_psstatus').show();
        }
    }
}
