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
        var valuelist = {Temperatures:"Temperatures [C]", Voltages:"Voltages [V]", Fans:"Fans [RPM]", Powers:"Powers [W]", Currents:"Currents [A]", Misc:"Misc [0/1]"};
        for (var ipmiinfo_value in valuelist) {
            if (data['Plugins']['Plugin_ipmiinfo'][ipmiinfo_value] !== undefined) { 
                var datas = items(data['Plugins']['Plugin_ipmiinfo'][ipmiinfo_value]["Item"]);
                if (datas.length > 0) {
                    data_ipmiinfo.push({Label:valuelist[ipmiinfo_value]});
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
