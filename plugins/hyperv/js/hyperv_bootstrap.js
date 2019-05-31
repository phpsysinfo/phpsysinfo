function renderPlugin_hyperv(data) {

    var directives = {
        State1: {
            text: function () {
                return (this.State === "2") ? "ON" : "";
            }
        },
        State0: {
            text: function () {
                return (this.State === "2") ? "" : "OFF";
            }
        }
    };

    if (data.Plugins.Plugin_HyperV !== undefined) {
        var hvitems = items(data.Plugins.Plugin_HyperV.Machine);
        if (hvitems.length > 0) {
            var hv_memory = [];
            hv_memory.push_attrs(hvitems);
            $('#hyperv-data').render(hv_memory, directives);
            $('#hyperv_Name').removeClass("sorttable_sorted"); // reset sort order
            sorttable.innerSortFunction.apply($('#hyperv_Name')[0], []);

            $('#block_hyperv').show();
        } else {
            $('#block_hyperv').hide();
        }
    } else {
        $('#block_hyperv').hide();
    }
}
