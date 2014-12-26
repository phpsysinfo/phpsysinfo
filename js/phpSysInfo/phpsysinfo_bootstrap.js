var data_dbg;

$(document).ready(function () {
    $(document).ajaxStart(function () {
        $("#loader").show();
    });
    $(document).ajaxStop(function () {
        $("#loader").hide();
    });

    $.ajax({
        dataType: "json",
        url: "xml.php?plugin=complete&json",
        success: function (data) {
            console.log(data);
            data_dbg = data;
            renderErrors(data);
            renderVitals(data);
            renderHardware(data);
            renderMemory(data);
            renderFilesystem(data);
            renderNetwork(data);
            renderVoltage(data);
            renderTemperature(data);
            renderFans(data);
            renderPower(data);
            renderCurrent(data);
            renderUPS(data);
            
            // Rendering plugins
            if (data['Plugins'] !== undefined) {

                for (plugin in data['Plugins']) {
                    if (data['Plugins'][plugin] !== undefined) {
                        try {
                            // dynamic call
                            window['render' + plugin](data);
                        }
                        catch (err) {
                        }
                    }
                }

            }
        }
    });
});

function items(data) {
    if (data !== undefined) {
        if ((data.length > 0) &&  (data[0] !== undefined) && (data[0]["@attributes"] !== undefined)) {
            return data;
        } else if (data["@attributes"] !== undefined ) {
            return [data];
        } else {
            return [];
        }
    } else {
        return [];
    }
}

function renderVitals(data) {
    var directives = {
        Uptime: {
            text: function () {
                return formatUptime(this["Uptime"]);
            }
        },
        LastBoot: {
            text: function () {
                var lastboot;
                var timestamp = 0;
                if ((data["Generation"] !== undefined) && (data["Generation"]["@attributes"] !== undefined) && (data["Generation"]["@attributes"]["timestamp"] !== undefined) ) {
                    timestamp = parseInt(data["Generation"]["@attributes"]["timestamp"])*1000; //server time
                    if (isNaN(timestamp)) timestamp = Number(new Date()); //client time
                } else {
                    timestamp = Number(new Date()); //client time
                }
                lastboot = new Date(timestamp - (parseInt(this["Uptime"])*1000));
                if (typeof(lastboot.toUTCString) === "function") {
                    return lastboot.toUTCString(); //toUTCstring() or toLocaleString()
                } else {
                //deprecated
                    return lastboot.toGMTString(); //toGMTString() or toLocaleString()
                }
            }
        },
        Distro: {
            html: function () {
                return '<img src="gfx/images/' + this["Distroicon"] + '" style="width:20px"/>' + " " +this["Distro"];
            }
        },
        LoadAvg: {
            html: function () {
                if (this["CPULoad"] !== undefined) {
                    return '<table width=100%><tr><td width=50%>'+this["LoadAvg"] + '</td><td><div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width: ' + round(this["CPULoad"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["CPULoad"],0) + '%</div></td></tr></table>';
                } else {
                    return this["LoadAvg"];
                }
            }
        },
        Processes: {
            text: function () {
                var processes = "", prunning = 0, psleeping = 0, pstopped = 0, pzombie = 0, pwaiting = 0, pother = 0;
                var not_first = false;
                processes = parseInt(this["Processes"]);
                if (this["ProcessesRunning"] !== undefined) {
                    prunning = parseInt(this["ProcessesRunning"]);
                }
                if (this["ProcessesSleeping"] !== undefined) {
                    psleeping = parseInt(this["ProcessesSleeping"]);
                }
                if (this["ProcessesStopped"] !== undefined) {
                    pstopped = parseInt(this["ProcessesStopped"]);
                }
                if (this["ProcessesZombie"] !== undefined) {
                    pzombie = parseInt(this["ProcessesZombie"]);
                }
                if (this["ProcessesWaiting"] !== undefined) {
                    pwaiting = parseInt(this["ProcessesWaiting"]);
                }
                if (this["ProcessesOther"] !== undefined) {
                    pother = parseInt(this["ProcessesOther"]);
                }
                if (prunning || psleeping || pstopped || pzombie || pwaiting || pother) {
                    processes += " (";
                    for (proc_type in {running:0,sleeping:1,stopped:2,zombie:3,waiting:4,other:5}) {
                        if (eval("p" + proc_type)) {
                            if (not_first) {
                                processes += "," + String.fromCharCode(160);
                            }
                            processes += eval("p" + proc_type) + String.fromCharCode(160) + proc_type;
                            not_first = true;
                        }
                    }
                    processes += ")";
                }            
                return processes;
            }
        }
    };

    if (data["Vitals"]["@attributes"]["SysLang"] === undefined) {
        $("#tr_SysLang").hide();
    }
    if (data["Vitals"]["@attributes"]["CodePage"] === undefined) {
        $("#tr_CodePage").hide();
    }
    if (data["Vitals"]["@attributes"]["Processes"] === undefined) {
        $("#tr_Processes").hide();
    }
    $('#vitals').render(data["Vitals"]["@attributes"], directives);
}

function renderHardware(data) {

    if ((data["Hardware"]["@attributes"] !== undefined) && (data["Hardware"]["@attributes"]["Name"] !== undefined)) {
        $('#hardware-Machine').render(data["Hardware"]["@attributes"]);
    } else {
        $('#hardware-Machine').hide();
    }

    var directives = {
        Model: {
            text: function () {
                return this["Model"];
            }
        },
        CpuSpeed: {
            text: function () {
                return formatHertz(this["CpuSpeed"]);
            }
        },
        CpuSpeedMax: {
            text: function () {
                return formatHertz(this["CpuSpeedMax"]);
            }
        },
        CpuSpeedMin: {
            text: function () {
                return formatHertz(this["CpuSpeedMin"]);
            }
        },
        Cache: {
            text: function () {
                return formatBytes(this["Cache"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        BusSpeed: {
            text: function () {
                return formatHertz(this["BusSpeed"]);
            }
        },
        Cputemp: {
            text: function () {
                return formatTemp(this["Cputemp"], data["Options"]["@attributes"]["tempFormat"]);
            }
        },
        Bogomips: {
            text: function () {
                return parseInt(this["Bogomips"]);
            }
        },
        Load: {
            html: function () {
                return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width: ' + round(this["Load"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["Load"],0) + '%</div>';
            }
        }
    };

    var hw_directives = {
        hwName: {
            text: function() {
                return this["Name"];
            }
        },
        hwCount: {
            text: function() {
                if (this["Count"] == "1") {
                    return "";
                }
                return this["Count"];
            }
        }
    };

    var html="";

    html+="<tr id=\"hardware-CPU\" class=\"treegrid-CPU\" style=\"display:none\" >";
    html+="<th>CPU</th>";
    html+="<td>Number of processors:</td>";
    html+="<td class=\"rightCell\"><span></span></td></td>";
    html+="</tr>";

    var paramlist = {CpuSpeed:"CPU Speed",CpuSpeedMax:"CPU Speed Max",CpuSpeedMin:"CPU Speed Min",Cache:"Cache Size",Virt:"Virtualization",BusSpeed:"BUS Speed",Bogomips:"System Bogomips",Cputemp:"Temperature",Load:"Load Averages"};
    try {
        var datas = items(data["Hardware"]["CPU"]["CpuCore"]);
        for (var i = 0; i < datas.length; i++) {
            html+="<tr id=\"hardware-CPU-" + i +"\" class=\"treegrid-CPU-" + i +" treegrid-parent-CPU\">";
            html+="<th></th>";
            html+="<td><span data-bind=\"Model\"></span></td>";
            html+="<td></td>";
            html+="</tr>";
            for (var proc_param in paramlist) {
                if (datas[i]["@attributes"][proc_param] !== undefined) {
                    html+="<tr id=\"hardware-CPU-" + i + "-" + proc_param + "\" class=\"treegrid-parent-CPU-" + i +"\">";
                    html+="<th></th>";
                    html+="<td>"+ paramlist[proc_param]+"</td>";
                    html+="<td class=\"rightCell\"><span data-bind=\"" + proc_param + "\"></span></td>";
                    html+="</tr>"; 
                }
            }

        }
    }
    catch (err) {
        $("#hardware-CPU").hide();
    }

    for (hw_type in {PCI:0,IDE:1,SCSI:2,USB:3}) {
        html+="<tr id=\"hardware-" + hw_type + "\" class=\"treegrid-" + hw_type + "\" style=\"display:none\" >";
        html+="<th>" + hw_type + "</th>";
        html+="<td>Number of devices:</td>";
        html+="<td class=\"rightCell\"><span></span></td></td>";
        html+="</tr>";

        try {
            var datas = items(data["Hardware"][hw_type]["Device"]);
            for (var i = 0; i < datas.length; i++) {
                html+="<tr id=\"hardware-" + hw_type + "-" + i +"\" class=\"treegrid-parent-" + hw_type + "\">";
                html+="<th></th>";
                html+="<td><span data-bind=\"hwName\"></span></td>";
                html+="<td class=\"rightCell\"><span data-bind=\"hwCount\"></span></td>";
                html+="</tr>";
            }
        }
        catch (err) {
            $("#hardware-"+hw_type).hide();
        }
    }
    $("#hardware").append(html);

    try {
        var datas = items(data["Hardware"]["CPU"]["CpuCore"]);
        for (var i = 0; i < datas.length; i++) {
            $('#hardware-CPU-'+ i).render(datas[i]["@attributes"]);
            for (var proc_param in paramlist) {
                if (datas[i]["@attributes"][proc_param] !== undefined) {
                    $('#hardware-CPU-'+ i +'-'+proc_param).render(datas[i]["@attributes"], directives);
                }
            }
        }
        if (i > 0) {
            $("#hardware-CPU span").html(i);
        }
    }
    catch (err) {
        $("#hardware-CPU").hide();
    }

    for (hw_type in {PCI:0,IDE:1,SCSI:2,USB:3}) {
        try {
            var licz = 0;
            var datas = items(data["Hardware"][hw_type]["Device"]);
            for (var i = 0; i < datas.length; i++) {
                $('#hardware-'+hw_type+'-'+ i).render(datas[i]["@attributes"], hw_directives);
                if (datas[i]["@attributes"]["Count"] !== undefined) {
                    licz += parseInt(datas[i]["@attributes"]["Count"]);
                } else {
                    licz++;
                }
            }
            if (i > 0) {
                $("#hardware-" + hw_type + " span").html(licz);
            }
        }
        catch (err) {
            $("#hardware-"+hw_type).hide();
        }
    }
    $('#hardware').treegrid({
        initialState: 'collapsed',
        expanderExpandedClass: 'normalicon normalicon-down',
        expanderCollapsedClass: 'normalicon normalicon-right'
    });
    if (data["Options"]["@attributes"]["showCPUListExpanded"] !== "false") {
        $('#hardware-CPU').treegrid('expand');
    }
    if (data["Options"]["@attributes"]["showCPUInfoExpanded"] === "true") {
        try {
            var datas = items(data["Hardware"]["CPU"]["CpuCore"]);
            for (var i = 0; i < datas.length; i++) {
                $('#hardware-CPU-'+i).treegrid('expand');
            }
        }
        catch (err) {
        }
    }
}

function renderMemory(data) {
    var directives = {
        Total: {
            text: function () {
                return formatBytes(this["@attributes"]["Total"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Free: {
            text: function () {
                return formatBytes(this["@attributes"]["Free"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Used: {
            text: function () {
                return formatBytes(this["@attributes"]["Used"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Usage: {
            html: function () {
                if ((this["Details"] === undefined) || (this["Details"]["@attributes"] === undefined)) {
                    return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width: ' + this["@attributes"]["Percent"] + '%;"></div>' +
                        '</div><div class="percent">' + this["@attributes"]["Percent"] + '%</div>';
                }
                else {
                    return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width: ' + this["Details"]["@attributes"]["AppPercent"] + '%;"></div>' +
                        '<div class="progress-bar progress-bar-warning" style="width: ' + this["Details"]["@attributes"]["CachedPercent"] + '%;"></div>' +
                        '<div class="progress-bar progress-bar-danger" style="width: ' + this["Details"]["@attributes"]["BuffersPercent"] + '%;"></div>' +
                        '</div>' +
                        '<div class="percent">' +
                        'Total: ' + this["@attributes"]["Percent"] + '% ' +
                        '<i>(App: ' + this["Details"]["@attributes"]["AppPercent"] + '% - ' +
                        'Cache: ' + this["Details"]["@attributes"]["CachedPercent"] + '% - ' +
                        'Buffers: ' + this["Details"]["@attributes"]["BuffersPercent"] + '%' +
                        ')</i></div>';
                }
            }
        },
        Type: {
            text: function () {
                return "Physical Memory";
            }
        }
    };

    var directive_swap = {
        Total: {
            text: function () {
                return formatBytes(this["Total"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Free: {
            text: function () {
                return formatBytes(this["Free"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Used: {
            text: function () {
                return formatBytes(this["Used"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Usage: {
            html: function () {
                return '<div class="progress">' +
                    '<div class="progress-bar progress-bar-info" style="width: ' + this["Percent"] + '%;"></div>' +
                    '</div><div class="percent">' + this["Percent"] + '%</div>';
            }
        },
        Name: {
            html: function () {
                return this['Name'] + '<br/>' + this['MountPoint'];
            }
        }
    };

    var data_memory = [];
    var datas = items(data["Memory"]["Swap"]["Mount"]);
    for (var i = 0; i < datas.length; i++) {
        data_memory.push(datas[i]["@attributes"]);
    }
    $('#memory-data').render(data["Memory"], directives);
    $('#swap-data').render(data_memory, directive_swap);
}

function renderFilesystem(data) {
    var directives = {
        Total: {
            text: function () {
                return formatBytes(this["Total"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Free: {
            text: function () {
                return formatBytes(this["Free"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Used: {
            text: function () {
                return formatBytes(this["Used"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        MountPoint: {
            text: function () {
                return ((this["MountPoint"] !== undefined) ? this["MountPoint"] : this["MountPointID"]);
            }
        },
        Name: {
            html: function () {
                return this["Name"] + ((this["MountOptions"] !== undefined) ? '<br><i>(' + this["MountOptions"] + ')</i>' : '');
            }
        },
        Percent: {
            html: function () {
                return '<div class="progress">' + '<div class="' +
                    (((data["Options"]["@attributes"]["threshold"] !== undefined) &&
                        (parseInt(this["Percent"]) >= parseInt(data["Options"]["@attributes"]["threshold"]))) ? 'progress-bar progress-bar-danger' : 'progress-bar progress-bar-info') +
                    '" style="width: ' + this["Percent"] + '% ;"></div>' +
                    '</div>' + '<div class="percent">' + this["Percent"] + '% ' + ((this["Inodes"] !== undefined) ? '<i>(' + this["Inodes"] + '%)</i>' : '') + '</div>';
            }
        }
    };

    try {
        var fs_data = [];
        var datas = items(data["FileSystem"]["Mount"]);
        for (var i = 0; i < datas.length; i++) {
            fs_data.push(datas[i]["@attributes"]);
        }
        if (i > 0) {
            $('#filesystem-data').render(fs_data, directives);
//            sorttable.innerSortFunction.apply(document.getElementById('filesystem_MountPoint'), []);
            sorttable.innerSortFunction.apply($('#filesystem_MountPoint')[0], []);
            $("#block_filesystem").show();
        } else {
            $("#block_filesystem").hide();
        }
    }
    catch (err) {
        $("#block_filesystem").hide();
    }
}


function renderNetwork(data) {
    var directives = {
        RxBytes: {
            text: function () {
                return formatBytes(this["RxBytes"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        TxBytes: {
            text: function () {
                return formatBytes(this["TxBytes"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Drops: {
            text: function () {
                return this["Drops"] + "/" + this["Err"];
            }
        }
    };

    var html = "";
    try {
        var network_data = [];
        var datas = items(data["Network"]["NetDevice"]);

        for (var i = 0; i < datas.length; i++) {
            html+="<tr id=\"network-" + i +"\" class=\"treegrid-network-" + i + "\">";
            html+="<td><b><span data-bind=\"Name\"></span></b></td>";
            html+="<td class=\"rightCell\"><span data-bind=\"RxBytes\"></span></td>";
            html+="<td class=\"rightCell\"><span data-bind=\"TxBytes\"></span></td>";
            html+="<td class=\"rightCell\"><span data-bind=\"Drops\"></span></td>";
            html+="</tr>";
          
            var info  = datas[i]["@attributes"]["Info"];
            if ( (info !== undefined) && (info !== "") ) {
                var infos = info.replace(/:/g, "&#8203:").split(";"); /* split long addresses */
                for(var j = 0; j < infos.length; j++){
                    html +="<tr class=\"treegrid-parent-network-" + i + "\"><td>" + infos[j] + "</td><td></td><td></td><td></td></tr>";
                }
            }
        }
        $("#network").append(html);
        if (i > 0) {
            for (var i = 0; i < datas.length; i++) {
                $('#network-' + i).render(datas[i]["@attributes"], directives);
            }
            $("#block_network").show();
        } else {
            $("#block_network").hide();
        }
    }
    catch (err) {
        $("#block_network").hide();
    }

    $('#network').treegrid({
        initialState: 'collapsed',
        expanderExpandedClass: 'normalicon normalicon-down',
        expanderCollapsedClass: 'normalicon normalicon-right'
    });

}

function renderVoltage(data) {
    var directives = {
        Value: {
            text: function () {
                return this["Value"] + String.fromCharCode(160) + "V";
            }
        },
        Min: {
            text: function () {
                if (this["Min"] !== undefined)
                    return this["Min"] + String.fromCharCode(160) + "V";
            }
        },
        Max: {
            text: function () {
                if (this["Max"] !== undefined)
                    return this["Max"] + String.fromCharCode(160) + "V";
            }
        },
        Label: {
            text: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " ! "+this["Event"];
            }
        }
    };
    try {
        var voltage_data = [];
        var datas = items(data["MBInfo"]["Voltage"]["Item"]);
        for (var i = 0; i < datas.length; i++) {
            voltage_data.push(datas[i]["@attributes"]);
        }
        if (i > 0) {
            $('#voltage-data').render(voltage_data, directives);
            $("#block_voltage").show();
        } else {
            $("#block_voltage").hide();
        }
    }
    catch (err) {
        $("#block_voltage").hide();
    }
}

function renderTemperature(data) {
    var directives = {
        Value: {
            text: function () {
                return formatTemp(this["Value"], data["Options"]["@attributes"]["tempFormat"]);
            }
        },
        Max: {
            text: function () {
                if (this["Max"] !== undefined)
                    return formatTemp(this["Max"], data["Options"]["@attributes"]["tempFormat"]);
            }
        },
        Label: {
            text: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " ! "+this["Event"];
            }
        }
    };

    try {
        var temperature_data = [];
        var datas = items(data["MBInfo"]["Temperature"]["Item"]);
        for (var i = 0; i < datas.length; i++) {
            temperature_data.push(datas[i]["@attributes"]);
        }
        if (i > 0) {
            $('#temperature-data').render(temperature_data, directives);
            $("#block_temperature").show();
        } else {
            $("#block_temperature").hide();
        }
    }
    catch (err) {
        $("#block_temperature").hide();
    }
}

function renderFans(data) {
    var directives = {
        Value: {
            text: function () {
                return this["Value"] + String.fromCharCode(160) + "RPM";
            }
        },
        Min: {
            text: function () {
                if (this["Min"] !== undefined)
                    return this["Min"] + String.fromCharCode(160) + "RPM";
            }
        },
        Label: {
            text: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " ! "+this["Event"];
            }
        }
    };

    try {
        var fans_data = [];
        var datas = items(data["MBInfo"]["Fans"]["Item"]);
        for (var i = 0; i < datas.length; i++) {
            fans_data.push(datas[i]["@attributes"]);
        }
        if (i > 0) {
            $('#fans-data').render(fans_data, directives);
            $("#block_fans").show();
        } else {
            $("#block_fans").hide();
        }
    }
    catch (err) {
        $("#block_fans").hide();
    }
}

function renderPower(data) {
    var directives = {
        Value: {
            text: function () {
                return this["Value"] + String.fromCharCode(160) + "W";
            }
        },
        Max: {
            text: function () {
                if (this["Max"] !== undefined)
                    return this["Max"] + String.fromCharCode(160) + "W";
            }
        },
        Label: {
            text: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " ! "+this["Event"];
            }
        }
    };

    try {
        var power_data = [];
        var datas = items(data["MBInfo"]["Power"]["Item"]);
        for (var i = 0; i < datas.length; i++) {
            power_data.push(datas[i]["@attributes"]);
        }
        if (i > 0) {
            $('#power-data').render(power_data, directives);
            $("#block_power").show();
        } else {
            $("#block_power").hide();
        }
    }
    catch (err) {
        $("#block_power").hide();
    }
}

function renderCurrent(data) {
    var directives = {
        Value: {
            text: function () {
                return this["Value"] + String.fromCharCode(160) + "A";
            }
        },
        Max: {
            text: function () {
                if (this["Max"] !== undefined)
                    return this["Max"] + String.fromCharCode(160) + "A";
            }
        },
        Label: {
            text: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " ! "+this["Event"];
            }
        }
    };

    try {
        var current_data = [];
        var datas = items(data["MBInfo"]["Current"]["Item"]);
        for (var i = 0; i < datas.length; i++) {
            current_data.push(datas[i]["@attributes"]);
        }
        if (i > 0) {
            $('#current-data').render(current_data, directives);
            $("#block_current").show();
        } else {
            $("#block_current").hide();
        }
    }
    catch (err) {
        $("#block_current").hide();
    }
}

function renderUPS(data) {

    var directives = {
        Name: {
            text: function () {
                return this["Name"] + ((this["Mode"] !== undefined) ? " (" + this["Mode"] + ")" : "");
            }
        },
        LineVoltage: {
            text: function () {
                return this["LineVoltage"] + String.fromCharCode(160) + "V";
            }
        },
        LineFrequency: {
            text: function () {
                return this["LineFrequency"] + String.fromCharCode(160) + "Hz";
            }
        },
        BatteryVoltage: {
            text: function () {
                return this["BatteryVoltage"] + String.fromCharCode(160) + "V";
            }
        },
        TimeLeftMinutes: {
            text: function () {
                return this["TimeLeftMinutes"] + String.fromCharCode(160) + "minutes";
            }
        },
        LoadPercent: {
            html: function () {
                return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width: ' + round(this["LoadPercent"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["LoadPercent"],0) + '%</div>';
            }
        },                
        BatteryChargePercent: {
            html: function () {
                return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width: ' + round(this["BatteryChargePercent"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["BatteryChargePercent"],0) + '%</div>';
            }
        } 
    };

    if ((data["UPSInfo"] === undefined) || (items(data["UPSInfo"]["UPS"]).length < 1)) {
        return;
    }

    var html="";
    var paramlist = {Model:"Model",StartTime:"Started",Status:"Status",Temperature:"Temperature",OutagesCount:"Outages",LastOutage:"Last outage cause",LastOutageFinish:"Last outage timestamp",LineVoltage:"Line voltage",LineFrequency:"Line frequency",LoadPercent:"Load percent",BatteryDate:"Battery date",BatteryVoltage:"Battery voltage",BatteryChargePercent:"Battery charge",TimeLeftMinutes:"Time left on batteries"};

    try {
        var datas = items(data["UPSInfo"]["UPS"]);
        for (var i = 0; i < datas.length; i++) {
            html+="<tr id=\"ups-" + i +"\" class=\"treegrid-UPS-" + i+ "\">";
            html+="<td width=60%><b><span data-bind=\"Name\"></span></b></td>";
            html+="<td></td>";
            html+="</tr>";
            for (var proc_param in paramlist) {
                if (datas[i]["@attributes"][proc_param] !== undefined) {
                    html+="<tr id=\"ups-" + i + "-" + proc_param + "\" class=\"treegrid-parent-UPS-" + i +"\">";
                    html+="<th>"+ paramlist[proc_param]+"</th>";
                    html+="<td class=\"rightCell\"><span data-bind=\"" + proc_param + "\"></span></td>";
                    html+="</tr>"; 
                }
            }

        }
    }
    catch (err) {
    }

    if ((data["UPSInfo"]["@attributes"] !== undefined) && (data["UPSInfo"]["@attributes"]["ApcupsdCgiLinks"] === "1")) {
        html+="<tr>";
        html+="<td>(<a href='/cgi-bin/apcupsd/multimon.cgi' target='apcupsdcgi'>Details</a>)</td>";
        html+="<td></td>";
        html+="</tr>";
    }
    
    $("#ups").append(html);

    try {
        var datas = items(data["UPSInfo"]["UPS"]);
        for (var i = 0; i < datas.length; i++) {
            $('#ups-'+ i).render(datas[i]["@attributes"], directives);
            for (var proc_param in paramlist) {
                if (datas[i]["@attributes"][proc_param] !== undefined) {
                    $('#ups-'+ i +'-'+proc_param).render(datas[i]["@attributes"], directives);
                }
            }
        }
    }
    catch (err) {
    }

    $('#ups').treegrid({
        initialState: 'expanded',
        expanderExpandedClass: 'normalicon normalicon-down',
        expanderCollapsedClass: 'normalicon normalicon-right'
    });
        
    $("#block_ups").show();

}

function renderErrors(data) {
    try {
        var datas = items(data["Errors"]["Error"]);
        for (var i = 0; i < datas.length; i++) { 
            $("#errors").append("<li><b>"+datas[i]["@attributes"]["Function"]+": </b>"+datas[i]["@attributes"]["Message"].replace(/\n/g, "<br>")+"</li>");
        }
        if (i > 0) {
            $("#errorbutton").show();
        } else {
            $("#errorbutton").hide();
        }
    }
    catch (err) {
        $("#errorbutton").hide();
    }
}

/**
 * format seconds to a better readable statement with days, hours and minutes
 * @param {Number} sec seconds that should be formatted
 * @return {String} html string with no breaking spaces and translation statemen
*/
function formatUptime(sec) {
    var txt = "", intMin = 0, intHours = 0, intDays = 0;
    intMin = sec / 60;
    intHours = intMin / 60;
    intDays = Math.floor(intHours / 24);
    intHours = Math.floor(intHours - (intDays * 24));
    intMin = Math.floor(intMin - (intDays * 60 * 24) - (intHours * 60));
    if (intDays) {
        txt += intDays.toString() + String.fromCharCode(160) + "days" + String.fromCharCode(160);
    }
    if (intHours) {
        txt += intHours.toString() + String.fromCharCode(160) + "hours" + String.fromCharCode(160);
    }
    return txt + intMin.toString() + String.fromCharCode(160) + "minutes";
}

/**
 * format a celcius temperature to fahrenheit and also append the right suffix
 * @param {String} degreeC temperature in celvius
 * @param {jQuery} xml phpSysInfo-XML
 * @return {String} html string with no breaking spaces and translation statements
 */
function formatTemp(degreeC, tempFormat) {
    var degree = 0;
    if (tempFormat === undefined) {
        tempFormat = "c";
    }
    degree = parseFloat(degreeC);
    if (isNaN(degreeC)) {
        return "---";
    }
    else {
        switch (tempFormat.toLowerCase()) {
        case "f":
            return round((((9 * degree) / 5) + 32), 1) + String.fromCharCode(160) + "F";
        case "c":
            return round(degree, 1) + String.fromCharCode(160) + "C";
        case "c-f":
            return round(degree, 1) + String.fromCharCode(160) + "C<br><i>(" + round((((9 * degree) / 5) + 32), 1) + String.fromCharCode(160) + "F)</i>";
        case "f-c":
            return round((((9 * degree) / 5) + 32), 1) + String.fromCharCode(160) + "F<br><i>(" + round(degree, 1) + String.fromCharCode(160) + "C)</i>";
        }
    }
}

/**
 * format a given MHz value to a better readable statement with the right suffix
 * @param {Number} mhertz mhertz value that should be formatted
 * @return {String} html string with no breaking spaces and translation statements
 */
function formatHertz(mhertz) {
    if (mhertz && mhertz < 1000) {
        return mhertz.toString() + String.fromCharCode(160) + "MHz";
    }
    else {
        if (mhertz && mhertz >= 1000) {
            return round(mhertz / 1000, 2) + String.fromCharCode(160) + "GHz";
        }
        else {
            return "";
        }
    }
}

/**
 * format the byte values into a user friendly value with the corespondenting unit expression<br>support is included
 * for binary and decimal output<br>user can specify a constant format for all byte outputs or the output is formated
 * automatically so that every value can be read in a user friendly way
 * @param {Number} bytes value that should be converted in the corespondenting format, which is specified in the phpsysinfo.ini
 * @param {jQuery} xml phpSysInfo-XML
 * @return {String} string of the converted bytes with the translated unit expression
 */
function formatBytes(bytes, byteFormat) {
    var show = "";

    if (byteFormat === undefined) {
        byteFormat = "auto_binary";
    }

    switch (byteFormat.toLowerCase()) {
    case "pib":
        show += round(bytes / Math.pow(1024, 5), 2);
        show += String.fromCharCode(160) + "PiB";
        break;
    case "tib":
        show += round(bytes / Math.pow(1024, 4), 2);
        show += String.fromCharCode(160) + "TiB";
        break;
    case "gib":
        show += round(bytes / Math.pow(1024, 3), 2);
        show += String.fromCharCode(160) + "GiB";
        break;
    case "mib":
        show += round(bytes / Math.pow(1024, 2), 2);
        show += String.fromCharCode(160) + "MiB";
        break;
    case "kib":
        show += round(bytes / Math.pow(1024, 1), 2);
        show += String.fromCharCode(160) + "KiB";
        break;
    case "pb":
        show += round(bytes / Math.pow(1000, 5), 2);
        show += String.fromCharCode(160) + "PB";
        break;
    case "tb":
        show += round(bytes / Math.pow(1000, 4), 2);
        show += String.fromCharCode(160) + "TB";
        break;
    case "gb":
        show += round(bytes / Math.pow(1000, 3), 2);
        show += String.fromCharCode(160) + "GB";
        break;
    case "mb":
        show += round(bytes / Math.pow(1000, 2), 2);
        show += String.fromCharCode(160) + "MB";
        break;
    case "kb":
        show += round(bytes / Math.pow(1000, 1), 2);
        show += String.fromCharCode(160) + "KB";
        break;
    case "b":
        show += bytes;
        show += String.fromCharCode(160) + "B";
        break;
    case "auto_decimal":
        if (bytes > Math.pow(1000, 5)) {
            show += round(bytes / Math.pow(1000, 5), 2);
            show += String.fromCharCode(160) + "PB";
        }
        else {
            if (bytes > Math.pow(1000, 4)) {
                show += round(bytes / Math.pow(1000, 4), 2);
                show += String.fromCharCode(160) + "TB";
            }
            else {
                if (bytes > Math.pow(1000, 3)) {
                    show += round(bytes / Math.pow(1000, 3), 2);
                    show += String.fromCharCode(160) + "GB";
                }
                else {
                    if (bytes > Math.pow(1000, 2)) {
                        show += round(bytes / Math.pow(1000, 2), 2);
                        show += String.fromCharCode(160) + "MB";
                    }
                    else {
                        if (bytes > Math.pow(1000, 1)) {
                            show += round(bytes / Math.pow(1000, 1), 2);
                            show += String.fromCharCode(160) + "KB";
                        }
                        else {
                                show += bytes;
                                show += String.fromCharCode(160) + "B";
                        }
                    }
                }
            }
        }
        break;
    default:
        if (bytes > Math.pow(1024, 5)) {
            show += round(bytes / Math.pow(1024, 5), 2);
            show += String.fromCharCode(160) + "PiB";
        }
        else {
            if (bytes > Math.pow(1024, 4)) {
                show += round(bytes / Math.pow(1024, 4), 2);
                show += String.fromCharCode(160) + "TiB";
            }
            else {
                if (bytes > Math.pow(1024, 3)) {
                    show += round(bytes / Math.pow(1024, 3), 2);
                    show += String.fromCharCode(160) + "GiB";
                }
                else {
                    if (bytes > Math.pow(1024, 2)) {
                        show += round(bytes / Math.pow(1024, 2), 2);
                        show += String.fromCharCode(160) + "MiB";
                    }
                    else {
                        if (bytes > Math.pow(1024, 1)) {
                            show += round(bytes / Math.pow(1024, 1), 2);
                            show += String.fromCharCode(160) + "KiB";
                        }
                        else {
                            show += bytes;
                            show += String.fromCharCode(160) + "B";
                        }
                    }
                }
            }
        }
    }
    return show;
}

/**
 * round a given value to the specified precision, difference to Math.round() is that there
 * will be appended Zeros to the end if the precision is not reached (0.1 gets rounded to 0.100 when precision is set to 3)
 * @param {Number} x value to round
 * @param {Number} n precision
 * @return {String}
 */
function round(x, n) {
    var e = 0, k = "";
    if (n < 0 || n > 14) {
        return 0;
    }
    if (n === 0) {
        return Math.round(x);
    } else {
        e = Math.pow(10, n);
        k = (Math.round(x * e) / e).toString();
        if (k.indexOf('.') === -1) {
            k += '.';
        }
        k += e.toString().substring(1);
        return k.substring(0, k.indexOf('.') + n + 1);
    }
}
