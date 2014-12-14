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

            // Rendering plugins
            if (data['Plugins'] != undefined) {

                for (plugin in data['Plugins']) {
                    // dynamic call
//                    window['renderPlugin' + plugin](data['Plugins'][plugin]);
                }

            }
        }
    });
});

function items(data) {
    if (data !== undefined) {
        if ((data.length > 0) && (data[0]["@attributes"] !== undefined)) {
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
                return secondsToString(this["Uptime"]);
            }
        },
        Distro: {
            html: function () {
                return '<img src="gfx/images/' + this["Distroicon"] + '" style="width:24px"/>' + this["Distro"];
            }
        }
    };

    if (data["Vitals"]["@attributes"]["SysLang"] === undefined) {
        $("#tr_SysLang").hide();
    }
    if (data["Vitals"]["@attributes"]["CodePage"] === undefined) {
        $("#tr_CodePage").hide();
    }
    $('#vitals').render(data["Vitals"]["@attributes"], directives);
}

function renderHardware(data) {
    var directives = {
        Model: {
            text: function () {
                if(this["CpuCore"].length > 1)
                    return this["CpuCore"].length + " x " + this["CpuCore"][0]["@attributes"]["Model"];
                else
                    return this["CpuCore"]["@attributes"]["Model"];
            }
        }
    };

    $('#hardware').render(data["Hardware"]["CPU"], directives);

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

//    for (hw_type of ["PCI","IDE","SCSI","USB"]) {
    for (hw_type in {PCI:"PCI",IDE:"IDE",SCSI:"SCSI",USB:"USB"}) {
        html+="<tr id=\"hardware-" + hw_type + "\" class=\"treegrid-" + hw_type + "\" style=\"display:none\" >";
        html+="<th>" + hw_type + "</th>";
        html+="<td>Number of devices:</td>";
        html+="<td class=\"rightCell\"><span>&nbsp;</span></td></td>";
        html+="</tr>";

        try {
            var datas = items(data["Hardware"][hw_type]["Device"]);
            for (var i = 0; i < datas.length; i++) {
                html+="<tr id=\"hardware-" + hw_type + "-" + i +"\" class=\"treegrid-parent-" + hw_type + "\" style=\"display:none\" >";
                html+="<th>&nbsp;</th>";
                html+="<td><span data-bind=\"hwName\">&nbsp;</span></td>";
                html+="<td class=\"rightCell\"><span data-bind=\"hwCount\">&nbsp;</span></td>";
                html+="</tr>";
            }
        }
        catch (err) {
            $("#hardware-"+hw_type).hide();
        }
    }
    $("#hardware").append(html);

//    for (hw_type of ["PCI","IDE","SCSI","USB"]) {
    for (hw_type in {PCI:"PCI",IDE:"IDE",SCSI:"SCSI",USB:"USB"}) {
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
    $('.tree').treegrid({
        initialState: 'collapsed',
        expanderExpandedClass: 'glyphicon glyphicon-minus',
        expanderCollapsedClass: 'glyphicon glyphicon-plus'
    });
}

function renderMemory(data) {
    var directives = {
        Total: {
            text: function () {
                return bytesToSize(this["@attributes"]["Total"]);
            }
        },
        Free: {
            text: function () {
                return bytesToSize(this["@attributes"]["Free"]);
            }
        },
        Used: {
            text: function () {
                return bytesToSize(this["@attributes"]["Used"]);
            }
        },
        Usage: {
            html: function () {
                if (this["Details"]["@attributes"] == undefined) {
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
                return bytesToSize(this["Total"]);
            }
        },
        Free: {
            text: function () {
                return bytesToSize(this["Free"]);
            }
        },
        Used: {
            text: function () {
                return bytesToSize(this["Used"]);
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
                return bytesToSize(this["Total"]);
            }
        },
        Free: {
            text: function () {
                return bytesToSize(this["Free"]);
            }
        },
        Used: {
            text: function () {
                return bytesToSize(this["Used"]);
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
                    ((!isNaN(data["Options"]["threshold"]) &&
                        (this["Percent"] >= data["Options"]["threshold"])) ? 'progress-bar progress-bar-danger' : 'progress-bar progress-bar-info') +
                    '" style="width: ' + this["Percent"] + '% ;"></div>' +
                    '</div>' + '<div class="percent">' + this["Percent"] + '% ' + (!isNaN(this["Inodes"]) ? '<i>(' + this["Inodes"] + '%)</i>' : '') + '</div>';
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
            sorttable.innerSortFunction.apply(document.getElementById('MountPoint'), []);
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
                return bytesToSize(this["RxBytes"]);
            }
        },
        TxBytes: {
            text: function () {
                return bytesToSize(this["TxBytes"]);
            }
        },
        Drops: {
            text: function () {
                return this["Drops"] + "/" + this["Err"];
            }
        }
    };

    try {
        var network_data = [];
        var datas = items(data["Network"]["NetDevice"]);
        for (var i = 0; i < datas.length; i++) {
            network_data.push(datas[i]["@attributes"]);
        }
        if (i > 0) {
            $('#network-data').render(network_data, directives);
            $("#block_network").show();
        } else {
            $("#block_network").hide();
        }
    }
    catch (err) {
        $("#block_network").hide();
    }
}

function renderVoltage(data) {
    var directives = {
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
                return this["Value"] + data["Options"]["@attributes"]["tempFormat"];
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

function renderErrors(data) {
    try {
        var datas = items(data["Errors"]["Error"]);
        for (var i = 0; i < datas.length; i++) {
            $("#errors").append("<li>"+datas[i]["@attributes"]["Message"]+"</li>");
        }
        if (i > 0) {
            $("#errorrow").show();
        } else {
            $("#errorrow").hide();
        }
    }
    catch (err) {
        $("#errorrow").hide();
    }
}

// from http://scratch99.com/web-development/javascript/convert-bytes-to-mb-kb/
function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return '0';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    if (i == 0) return bytes + '' + sizes[i];
    return (bytes / Math.pow(1024, i)).toFixed(1) + '' + sizes[i];
}

function secondsToString(seconds) {
    var numyears = Math.floor(seconds / 31536000);
    var numdays = Math.floor((seconds % 31536000) / 86400);
    var numhours = Math.floor(((seconds % 31536000) % 86400) / 3600);
    var numminutes = Math.floor((((seconds % 31536000) % 86400) % 3600) / 60);
    var numseconds = Math.floor((((seconds % 31536000) % 86400) % 3600) % 60);
    return numyears + " years " + numdays + " days " + numhours + " hours " + numminutes + " minutes " + numseconds + " seconds";
}
