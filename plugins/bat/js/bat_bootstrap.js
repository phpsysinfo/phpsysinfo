function renderPlugin_bat(data) {
    var batcount = 0;
    var directives = {
        Name: {
            text: function () {
                return (this["Name"] !== undefined) ? this["Name"] : 'Battery'+(batcount++);
            }
        },
        DesignCapacity: {
            html: function () {
                var CapacityUnit = (this["CapacityUnit"] !== undefined) ? this["CapacityUnit"] : 'mWh';
                return this["DesignCapacity"] + String.fromCharCode(160) + CapacityUnit;
            }
        },
        FullCapacity: {
            html: function () {
                var CapacityUnit = (this["CapacityUnit"] !== undefined) ? this["CapacityUnit"] : 'mWh';
                return this["FullCapacity"] + String.fromCharCode(160) + CapacityUnit;
            }
        },
        FullCapacityBar: {
            html: function () {
                var CapacityUnit = (this["CapacityUnit"] !== undefined) ? this["CapacityUnit"] : 'mWh';
                if (( CapacityUnit !== "%" ) && (this["DesignCapacity"] !== undefined)){
                    var percent = (this["DesignCapacity"] != 0) ? round(100*this["FullCapacity"]/this["DesignCapacity"],0) : 0;
                    return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + percent + '%;"></div>' +
                        '</div><div class="percent">' + percent + '%</div>';
                } else {
                    return '';
                }
            }
        },
        RemainingCapacity: {
            html: function () {
                var CapacityUnit = (this["CapacityUnit"] !== undefined) ? this["CapacityUnit"] : 'mWh';
                if ( CapacityUnit === "%" ) {
                    return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + round(this["RemainingCapacity"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["RemainingCapacity"],0) + '%</div>';
                } else {
                    return this["RemainingCapacity"] + String.fromCharCode(160) + CapacityUnit;
                }
            }
        },
        RemainingCapacityBar: {
            html: function () {
                var CapacityUnit = (this["CapacityUnit"] !== undefined) ? this["CapacityUnit"] : 'mWh';
                if (( CapacityUnit !== "%" ) && (this["FullCapacity"] !== undefined)){
                    var percent = (this["FullCapacity"] != 0) ? round(100*this["RemainingCapacity"]/this["FullCapacity"],0) : 0;
                    return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + percent + '%;"></div>' +
                        '</div><div class="percent">' + percent + '%</div>';
                } else {
                    return '';
                }
            }
        },
        PresentVoltage: {
            text: function () {
                return this['PresentVoltage'] + String.fromCharCode(160) + 'mV';
            }
        },
        BatteryTemperature: {
            html: function () {
                return formatTemp(this["BatteryTemperature"], data["Options"]["@attributes"]["tempFormat"]);
            }
        },
        DesignVoltage: {
            text: function () {
                return this['DesignVoltage']+String.fromCharCode(160) + 'mV';
            }
        },
        DesignVoltageMax: {
            text: function () {
                return (this["DesignVoltageMax"] !== undefined) ? this['DesignVoltageMax']+String.fromCharCode(160) + 'mV' : '';
            }
        }
    };

    if (data['Plugins']['Plugin_BAT'] !== undefined) {
        var bats = items(data['Plugins']['Plugin_BAT']['Bat']);
        if (bats.length > 0) {
            var html = "";
            var paramlist = {DesignCapacity:2,FullCapacity:13,RemainingCapacity:3,ChargingState:8,DesignVoltage:4,PresentVoltage:5,BatteryType:9,BatteryTemperature:10,BatteryCondition:11,CycleCount:12,BatteryManufacturer:14};
            var paramlis2 = {FullCapacity:'FullCapacityBar',RemainingCapacity:'RemainingCapacityBar',DesignVoltage:'DesignVoltageMax'};

            for (var i = 0; i < bats.length; i++) {
                try {
                    html+="<tr id=\"bat-" + i + "\" class=\"treegrid-bat-" + i + "\" style=\"display:none;\" >";
                    html+="<td><span class=\"treegrid-spanbold\" data-bind=\"Name\"></span></td>";
                    html+="<td></td>";
                    html+="<td></td>";
                    html+="</tr>";
                    for (var proc_param in paramlist) {
                        if (bats[i]["@attributes"][proc_param] !== undefined) {
                            html+="<tr id=\"bat-" + i + "-" + proc_param + "\" class=\"treegrid-parent-bat-" + i + "\">";
                            html+="<td><span class=\"treegrid-spanbold\">" + genlang(paramlist[proc_param], true, 'bat') + "</span></td>";
                            html+="<td><span data-bind=\"" + proc_param + "\"></span></td>";
                            if (paramlis2[proc_param] !== undefined) {
                                html+="<td class=\"rightCell\"><span data-bind=\"" + paramlis2[proc_param] + "\"></span></td>";
                            } else {
                                html+="<td></td>";
                            }
                            html+="</tr>";
                        }
                    }
                }
                catch (err) {
                   $("#bat-" + i).hide();
                }
            }

            $("#bat-data").empty().append(html);

            for (var i = 0; i < bats.length; i++) {
                try {
                    $('#bat-'+ i).render(bats[i]["@attributes"], directives);
                    $("#bat-" + i).show();
                    for (var proc_param in paramlist) {
                        if (bats[i]["@attributes"][proc_param] !== undefined) {
                            $('#bat-'+ i+ "-" + proc_param).render(bats[i]["@attributes"], directives);
                        }
                    }
                }
                catch (err) {
                   $("#bat-" + i).hide();
                }
            }

            $('#bat').treegrid({
                initialState: 'expanded',
                expanderExpandedClass: 'normalicon normalicon-down',
                expanderCollapsedClass: 'normalicon normalicon-right'
            });

            $('#block_bat').show();
        } else {
            $('#block_bat').hide();
        }
    } else {
        $('#block_bat').hide();
    }

/*



    if (data['Plugins']['Plugin_BAT'] !== undefined) {
        var batitems = items(data['Plugins']['Plugin_BAT']["Bat"]);
        if (batitems.length > 0) {
            for (var i = 0; i < batitems.length ; i++) {
                var attr = batitems[i]['@attributes'];
                $('#bat').render(attr, directives);
                for (bat_param in {DesignCapacity:0,FullCapacity:1,RemainingCapacity:2,ChargingState:3,DesignVoltage:4,PresentVoltage:5,BatteryType:6,BatteryTemperature:7,BatteryCondition:8,CycleCount:9,BatteryManufacturer:10}) {
                    if (attr[bat_param] !== undefined) {
                      $('#bat_' + bat_param).show();
                    }
                }
                if (attr["CapacityUnit"] === "%") {
                    $('#bat_DesignCapacity').hide();
                    $('#bat_FullCapacity').hide();
                }
            }
            $('#block_bat').show();
        } else {
            $('#block_bat').hide();
        }
    } else {
        $('#block_bat').hide();
    }*/
}
