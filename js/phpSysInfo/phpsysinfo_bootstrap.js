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

//    for (hw_type in data["Hardware"]) {
    for (hw_type in {PCI:"PCI",IDE:"IDE",SCSI:"SCSI",USB:"USB"}) {
        if (hw_type != "CPU") {
            try {
                hw_data = [];
                if (data["Hardware"][hw_type]["Device"].length > 0) {
                    for (i=0; i < data["Hardware"][hw_type]["Device"].length; i++) {
                        hw_data.push(data["Hardware"][hw_type]["Device"][i]["@attributes"]);
                    }
                } else if (data["Hardware"][hw_type]["Device"]["@attributes"] !== undefined) {
                    hw_data.push(data["Hardware"][hw_type]["Device"]["@attributes"]);
                }
                if (hw_data.length > 0) {
                    $("#hardware-" + hw_type + " span").html(hw_data.length);
                    $("#hw-dialog-"+hw_type+" ul").render(hw_data, hw_directives);
                    $("#hardware-"+hw_type).show();
                }
                else {
                    $("#hardware-"+hw_type).hide();
                }
            }
            catch (err) {
                $("#hardware-"+hw_type).hide();
            }
        }
    }
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

    if (data["Memory"]["Swap"]["Mount"].length > 0) {
        for (var i = 0; i < data["Memory"]["Swap"]["Mount"].length; i++) {
            data_memory.push(data["Memory"]["Swap"]["Mount"][i]["@attributes"]);
        }
    } else if (data["Memory"]["Swap"]["Mount"] !== undefined) {
        data_memory.push(data["Memory"]["Swap"]["Mount"]["@attributes"]);
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
        if (data["FileSystem"]["Mount"].length > 0) {
            for (var i = 0; i < data["FileSystem"]["Mount"].length; i++) {
                fs_data.push(data["FileSystem"]["Mount"][i]["@attributes"]);
            }
        } else if (data["FileSystem"]["Mount"] !== undefined) {
            fs_data.push(data["FileSystem"]["Mount"]["@attributes"]);
        }
        $('#filesystem-data').render(fs_data, directives);
        sorttable.innerSortFunction.apply(document.getElementById('MountPoint'), []);
        $("#block_filesystem").show();
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
        if (data["Network"]["NetDevice"].length > 0) {
            for (var i = 0; i < data["Network"]["NetDevice"].length; i++) {
                network_data.push(data["Network"]["NetDevice"][i]["@attributes"]);
            }
        } else if (data["Network"]["NetDevice"] !== undefined) {
            network_data.push(data["Network"]["NetDevice"]["@attributes"]);
        }
        $('#network-data').render(network_data, directives);
        $("#block_network").show();
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
        if (data["MBInfo"]["Voltage"]["Item"].length > 0) {
            for (var i = 0; i < data["MBInfo"]["Voltage"]["Item"].length; i++) {
                voltage_data.push(data["MBInfo"]["Voltage"]["Item"][i]["@attributes"]);
            }
        } else if (data["MBInfo"]["Voltage"]["Item"] !== undefined) {
            voltage_data.push(data["MBInfo"]["Voltage"]["Item"]["@attributes"]);
        }
        $('#voltage-data').render(voltage_data, directives);
        $("#block_voltage").show();
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
        if (data["MBInfo"]["Temperature"]["Item"].length > 0) {
            for (var i = 0; i < data["MBInfo"]["Temperature"]["Item"].length; i++) {
                temperature_data.push(data["MBInfo"]["Temperature"]["Item"][i]["@attributes"]);
            }
        } else if (data["MBInfo"]["Temperature"]["Item"] !== undefined) {
            temperature_data.push(data["MBInfo"]["Temperature"]["Item"]["@attributes"]);
        }
        $('#temperature-data').render(temperature_data, directives);
        $("#block_temperature").show();
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
        if (data["MBInfo"]["Fans"]["Item"].length > 0) {
            for (var i = 0; i < data["MBInfo"]["Fans"]["Item"].length; i++) {
                fans_data.push(data["MBInfo"]["Fans"]["Item"][i]["@attributes"]);
            }
        } else if (data["MBInfo"]["Fans"]["Item"] !== undefined) {
            fans_data.push(data["MBInfo"]["Fans"]["Item"]["@attributes"]);
        }
        $('#fans-data').render(fans_data, directives);
        $("#block_fans").show();
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
        if (data["MBInfo"]["Power"]["Item"].length > 0) {
            for (var i = 0; i < data["MBInfo"]["Power"]["Item"].length; i++) {
                power_data.push(data["MBInfo"]["Power"]["Item"][i]["@attributes"]);
            }
        } else if (data["MBInfo"]["Power"]["Item"] !== undefined) {
            power_data.push(data["MBInfo"]["Power"]["Item"]["@attributes"]);
        }
        $('#power-data').render(power_data, directives);
        $("#block_power").show();
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
        if (data["MBInfo"]["Current"]["Item"].length > 0) {
            for (var i = 0; i < data["MBInfo"]["Current"]["Item"].length; i++) {
                current_data.push(data["MBInfo"]["Current"]["Item"][i]["@attributes"]);
            }
        } else if (data["MBInfo"]["Current"]["Item"] !== undefined) {
            current_data.push(data["MBInfo"]["Current"]["Item"]["@attributes"]);
        }
        $('#current-data').render(current_data, directives);
        $("#block_current").show();
    }
    catch (err) {
        $("#block_current").hide();
    }
}

function renderErrors(data) {
    try {
        if (data["Errors"]["Error"] !== undefined) {
            if (data["Errors"]["Error"].length > 0) {
                $("#errorrow").show();
                for (var i = 0; i < data["Errors"]["Error"].length; i++) {
                    $("#errors").append("<li>"+data["Errors"]["Error"][i]["@attributes"]["Message"]+"</li>");
                }
            } else {
                $("#errors").append("<li>"+data["Errors"]["Error"]["@attributes"]["Message"]+"</li>");
                $("#errorrow").show();
            }
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
