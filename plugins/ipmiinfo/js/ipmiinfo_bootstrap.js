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
        var valuelist = {Temperatures:3, Voltages:4, Fans:5, Powers:8, Currents:7, Misc:6};
        for (var ipmiinfo_value in valuelist) {
            if (data['Plugins']['Plugin_ipmiinfo'][ipmiinfo_value] !== undefined) { 
                var datas = items(data['Plugins']['Plugin_ipmiinfo'][ipmiinfo_value]["Item"]);
                if (datas.length > 0) {
                    data_ipmiinfo.push({Label:genlang(valuelist[ipmiinfo_value], false ,'ipmiinfo')});
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
