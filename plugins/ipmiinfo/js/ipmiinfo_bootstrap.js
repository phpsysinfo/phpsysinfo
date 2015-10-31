function renderPlugin_ipmiinfo(data) {

    var directives = {
        Label: {
            html: function () {
                if (this["Value"] == undefined) {
                    return '<b>' + this["Label"] + '</b>';
                } else {
                   return this["Label"];
                }
            }
        }
    };

    if (data['Plugins']['Plugin_ipmiinfo'] !== undefined) { 
        var data_ipmiinfo = [];
        var valuelist = {Temperatures:"plugin_ipmiinfo_003", Voltages:"plugin_ipmiinfo_004", Fans:"plugin_ipmiinfo_005", Powers:"plugin_ipmiinfo_008", Currents:"plugin_ipmiinfo_007", Misc:"plugin_ipmiinfo_006"};
        for (var ipmiinfo_value in valuelist) {
            if (data['Plugins']['Plugin_ipmiinfo'][ipmiinfo_value] !== undefined) { 
                var datas = items(data['Plugins']['Plugin_ipmiinfo'][ipmiinfo_value]["Item"]);
                if (datas.length > 0) {
                    data_ipmiinfo.push(getTranslationString({Label:valuelist[ipmiinfo_value]},'ipmii'));
                    data_ipmiinfo.push_attrs(datas);
                }
            }
        }
        if (data_ipmiinfo.length > 0) {
            $('#ipmiinfo-data').render(data_ipmiinfo, directives);
            $('#block_ipmiinfo').show();
        } else {
            $('#block_ipmiinfo').hide();
        }
    } else {
        $('#block_ipmiinfo').hide();
    }
}
