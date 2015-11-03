function renderPlugin_bat(data) {

    var directives = {
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
        DesignCapacity: {
            html: function () {
                var CapacityUnit = (this["CapacityUnit"] !== undefined) ? this["CapacityUnit"] : 'mWh';
                return this["DesignCapacity"] + String.fromCharCode(160) + CapacityUnit;
            }
        },
        RemainingCapacityBar: {
            html: function () {
                var CapacityUnit = (this["CapacityUnit"] !== undefined) ? this["CapacityUnit"] : 'mWh';
                if (( CapacityUnit !== "%" ) && (this["DesignCapacity"] !== undefined)){
                    var percent = (this["DesignCapacity"] != 0) ? round(100*this["RemainingCapacity"]/this["DesignCapacity"],0) : 0;
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

    if ((data['Plugins']['Plugin_BAT'] !== undefined) && (data['Plugins']['Plugin_BAT']["Bat"] !== undefined) && (data['Plugins']['Plugin_BAT']["Bat"]["@attributes"] !== undefined)){
        $('#bat').render(data['Plugins']['Plugin_BAT']["Bat"]["@attributes"], directives);
        var attr = data['Plugins']['Plugin_BAT']["Bat"]["@attributes"];
        for (bat_param in {DesignCapacity:0,RemainingCapacity:1,ChargingState:2,DesignVoltage:3,PresentVoltage:4,BatteryType:5,BatteryTemperature:6,BatteryCondition:7,CycleCount:8}) {
            if (attr[bat_param] !== undefined) {
                $('#bat_' + bat_param).show();
            }
        }
        if (attr["CapacityUnit"] === "%") {
            $('#bat_DesignCapacity').hide();
        }
        $('#block_bat').show();
    } else {
        $('#block_bat').hide();
    }
}
