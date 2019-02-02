function renderPlugin_bat(data) {
    var batcount = 0;
    var directives = {
        Name: {
            text: function () {
                return (this.Name !== undefined) ? this.Name : 'Battery'+(batcount++);
            }
        },
        DesignCapacity: {
            html: function () {
                return this.DesignCapacity + String.fromCharCode(160) + this.CapacityUnit;
            }
        },
        FullCapacity: {
            html: function () {
                return this.FullCapacity + String.fromCharCode(160) + this.CapacityUnit;
            }
        },
        FullCapacityBar: {
            html: function () {
                if (( this.CapacityUnit !== "%" ) && (this.DesignCapacity !== undefined)){
                    var percent = (this.DesignCapacity > 0) ? round(100*this.FullCapacity/this.DesignCapacity,0) : 0;
                    return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + percent + '%;"></div>' +
                        '</div><div class="percent">' + percent + '%</div>';
                } else {
                    return '';
                }
            }
        },
        RemainingCapacity: {
            html: function () {
                if ( this.CapacityUnit === "%" ) {
                    return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + round(this.RemainingCapacity,0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this.RemainingCapacity,0) + '%</div>';
                } else {
                    return this.RemainingCapacity + String.fromCharCode(160) + this.CapacityUnit;
                }
            }
        },
        RemainingCapacityBar: {
            html: function () {
                if (( this.CapacityUnit !== "%" ) && (this.FullCapacity !== undefined)){
                    var percent = (this.FullCapacity > 0) ? round(100*this.RemainingCapacity/this.FullCapacity,0) : 0;
                    return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + percent + '%;"></div>' +
                        '</div><div class="percent">' + percent + '%</div>';
                } else {
                    return '';
                }
            }
        },
        PresentVoltage: {
            text: function () {
                return this.PresentVoltage + String.fromCharCode(160) + 'mV';
            }
        },
        BatteryTemperature: {
            html: function () {
                return formatTemp(this.BatteryTemperature, data.Options["@attributes"].tempFormat);
            }
        },
        DesignVoltage: {
            text: function () {
                return this.DesignVoltage + String.fromCharCode(160) + 'mV';
            }
        },
        DesignVoltageMax: {
            text: function () {
                return (this.DesignVoltageMax !== undefined) ? this.DesignVoltageMax + String.fromCharCode(160) + 'mV' : '';
            }
        }
    };

    if (data.Plugins.Plugin_BAT !== undefined) {
        var bats = items(data.Plugins.Plugin_BAT.Bat);
        if (bats.length > 0) {
            var html = "";
            var paramlist = {Model:15,Manufacturer:14,SerialNumber:16,DesignCapacity:2,FullCapacity:13,RemainingCapacity:3,ChargingState:8,DesignVoltage:4,PresentVoltage:5,BatteryType:9,BatteryTemperature:10,BatteryCondition:11,CycleCount:12};
            var paramlis2 = {FullCapacity:'FullCapacityBar',RemainingCapacity:'RemainingCapacityBar',DesignVoltage:'DesignVoltageMax'};
            var i, proc_param;

            for (i = 0; i < bats.length; i++) {
                if (bats[i]["@attributes"].CapacityUnit === undefined) {
                    bats[i]["@attributes"].CapacityUnit = 'mWh';
                } else if ((bats[i]["@attributes"].CapacityUnit === '%') && (bats[i]["@attributes"].RemainingCapacity !== undefined)) {
                   if (bats[i]["@attributes"].DesignCapacity !== undefined) {
                       delete bats[i]["@attributes"].DesignCapacity;
                   }
                   if (bats[i]["@attributes"].FullCapacity !== undefined) {
                       delete bats[i]["@attributes"].FullCapacity;
                   }
                }

                try {
                    html+="<tr id=\"bat-" + i + "\" class=\"treegrid-bat-" + i + "\" style=\"display:none;\" >";
                    html+="<td colspan=\"3\"><span class=\"treegrid-spanbold\" data-bind=\"Name\"></span></td>";
                    html+="</tr>";
                    for (proc_param in paramlist) {
                        if (bats[i]["@attributes"][proc_param] !== undefined) {
                            html+="<tr id=\"bat-" + i + "-" + proc_param + "\" class=\"treegrid-parent-bat-" + i + "\">";
                            html+="<td><span class=\"treegrid-spanbold\">" + genlang(paramlist[proc_param], 'bat') + "</span></td>";
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

            for (i = 0; i < bats.length; i++) {
                try {
                    $('#bat-'+ i).render(bats[i]["@attributes"], directives);
                    $("#bat-" + i).show();
                    for (proc_param in paramlist) {
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
}
