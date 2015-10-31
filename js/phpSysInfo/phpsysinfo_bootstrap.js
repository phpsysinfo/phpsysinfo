//var data_dbg;
/**
 * load the given translation an translate the entire page<br><br>retrieving the translation is done through a
 * ajax call
 * @private
 * @param {String} lang language for which the translation should be loaded
 * @param {String} plugin if plugin is given, the plugin translation file will be read instead of the main translation file
 * @param {String} plugname internal plugin name
 * @return {jQuery} translation jQuery-Object
 */
var langxml = [], langcounter = 1, langarr = [];

function getLanguage(lang, plugin, plugname) {
    var getLangUrl = "";
    if (lang) {
        getLangUrl = 'language/language.php?lang=';
        if (plugin) {
            getLangUrl += "&plugin=" + plugin;
        }
    }
    else {
        getLangUrl = 'language/language.php';
        if (plugin) {
            getLangUrl += "?plugin=" + plugin;
        }
    }
    $.ajax({
        url: getLangUrl,
        type: 'GET',
        dataType: 'xml',
        timeout: 100000,
        async: false,
        error: function error() {
            $.jGrowl("Error loading language!");
        },
        success: function buildblocks(xml) {
            var idexp;
            langxml[plugname] = xml;
            if (langarr[plugname] === undefined) {
                langarr.push(plugname);
                langarr[plugname] = [];
            }
            $("expression", langxml[plugname]).each(function langstore(id) {
                idexp = $("expression", xml).get(id);
                langarr[plugname][this.getAttribute('id')] = $("exp", idexp).text().toString().replace(/\//g, "\/&#8203;");
            });
        }
    });
}

/**
 * internal function to get a given translation out of the translation file
 * @param {Number} langId id of the translation expression
 * @param {String} [plugin] name of the plugin
 * @return {String} translation string
 */
function getTranslationString(langId, plugin) {
    var plugname = "_";
    if (plugin === undefined) {
        plugname += "phpSysInfo";
    }
    else {
        plugname += plugin;
    }
    if (langxml[plugname] === undefined) {
        langxml.push(plugname);
        getLanguage(null, plugin, plugname);
    }
    return langarr[plugname][langId.toString()];
}

/**
 * generate a span tag with an unique identifier to be html valid
 * @param {Number} id translation id in the xml file
 * @param {Boolean} generate generate lang_id in span tag or use given value
 * @param {String} [plugin] name of the plugin for which the tag should be generated
 * @return {String} string which contains generated span tag for translation string
 */

function genlang(id, plugin) {
    var html = "", idString = "", plugname = "";
    if (plugin === undefined) {
        plugname = "";
    }
    else {
        plugname = plugin.toLowerCase();
    }
    if (plugin) {
        idString = "plugin_" + plugname + "_" + id;
    }
    translation = getTranslationString(idString, plugin);
    return translation;
}

/**
 * translates all expressions based on the translation xml file<br>
 * translation expressions must be in the format &lt;span id="lang???"&gt;&lt;/span&gt;, where ??? is
 * the number of the translated expression in the xml file<br><br>if a translated expression is not found in the xml
 * file nothing would be translated, so the initial value which is inside the span tag is displayed
 * @param {String} [plugin] name of the plugin
 */
function changeLanguage(plugin) {
    var langId = "", langStr = "",text="";
    var pos=0;

	if(plugin== undefined)	{
		text='span[id*=lang_]';
		pos=5;
	}
	else	{
		text='span[id*='+plugin+'_]';
		pos=1+plugin.length;
	}
	$(text).each(function translate(i) {
	langId = this.getAttribute('id').substring(pos);
	if (langId.indexOf('-') !== -1) {
		langId = langId.substring(0, langId.indexOf('-')); //remove the unique identifier
	}
	if(plugin==undefined){
		langStr = getTranslationString(langId, plugin);
	}
	else	{
		langStr = genlang(langId, plugin);
	}
	if (langStr !== undefined) {
		if (langStr.length > 0) {
			this.innerHTML = langStr;
		}
	}
        });
}


function reload(initiate) {
    $("#errorbutton").hide();
    $("#errors").empty();
    $.ajax({
        dataType: "json",
        url: "xml.php?json",
        success: function (data) {
//            console.log(data);
//            data_dbg = data;
            if ((initiate === true) && (data["Options"] !== undefined) && (data["Options"]["@attributes"] !== undefined) 
                && ((refrtime = data["Options"]["@attributes"]["refresh"]) !== undefined) && (refrtime !== "0")) {
                setInterval(reload, refrtime);
            }
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
            changeLanguage();
			
            if (data['UnusedPlugins'] !== undefined) {
                var plugins = items(data["UnusedPlugins"]["Plugin"]);
                for (var i = 0; i < plugins.length; i++) {
                    $.ajax({
                         dataType: "json",
                         url: "xml.php?plugin=" + plugins[i]["@attributes"]["name"]+"&json",
                         pluginname: plugins[i]["@attributes"]["name"],
                         success: function (data) {
                            try {
                                // dynamic call
                                window['renderPlugin_' + this.pluginname](data);
                                changeLanguage(this.pluginname);

                            }
                            catch (err) {
                            }
                            renderErrors(data);
                        }
                    });
                }
            }
        }
    });
}

$(document).ready(function () {
    $(document).ajaxStart(function () {
        $("#loader").show();
    });
    $(document).ajaxStop(function () {
        $("#loader").hide();
    });

    $.getScript( "./js.php?name=bootstrap", function(data, status, jqxhr) {
        reload(true);

        $(".navbar-logo").click(function () {
            reload();
        });
    });

});

Array.prototype.push_attrs=function(element) {
    for (var i = 0; i < element.length ; i++) {
        this.push(element[i]["@attributes"]);
    }
    return i;
};

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
                return '<img src="gfx/images/' + this["Distroicon"] + '" style="width:32px"/>' + " " +this["Distro"];
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
                var processes = "", p111 = 0, p112 = 0, p113 = 0, p114 = 0, p115 = 0, p116 = 0;
                var not_first = false;
                processes = parseInt(this["Processes"]);
                if (this["ProcessesRunning"] !== undefined) {
                    p111 = parseInt(this["ProcessesRunning"]);
                }
                if (this["ProcessesSleeping"] !== undefined) {
                    p112 = parseInt(this["ProcessesSleeping"]);
                }
                if (this["ProcessesStopped"] !== undefined) {
                    p113 = parseInt(this["ProcessesStopped"]);
                }
                if (this["ProcessesZombie"] !== undefined) {
                    p114 = parseInt(this["ProcessesZombie"]);
                }
                if (this["ProcessesWaiting"] !== undefined) {
                    p115 = parseInt(this["ProcessesWaiting"]);
                }
                if (this["ProcessesOther"] !== undefined) {
                    p116 = parseInt(this["ProcessesOther"]);
                }
                if (p111 || p112 || p113 || p114 || p115 || p116) {
                    processes += " (";
                    for (proc_type in {111:0,112:1,113:2,114:3,115:4,116:5}) {
                        if (eval("p" + proc_type)) {
                            if (not_first) {
                                processes += ", ";
                            }
                            processes += eval("p" + proc_type) + String.fromCharCode(160) + getTranslationString(proc_type,false);
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
            html: function () {
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

    if ((data["Hardware"]["@attributes"] !== undefined) && (data["Hardware"]["@attributes"]["Name"] !== undefined)) {
        html+="<tr id=\"hardware-Machine\">";
        html+="<th width=8%>"+getTranslationString('010',false)+"</th>"; // Machine
        html+="<td><span data-bind=\"Name\"></span></td>";
        html+="<td></td>";
        html+="</tr>";
    }

    var paramlist = {CpuSpeed:"013",CpuSpeedMax:"100",CpuSpeedMin:"101",Cache:"015",Virt:"094",BusSpeed:"014",Bogomips:"016",Cputemp:"051",Load:"009"};
    try {
        var datas = items(data["Hardware"]["CPU"]["CpuCore"]);
        for (var i = 0; i < datas.length; i++) {
             if (i == 0) {
                html+="<tr id=\"hardware-CPU\" class=\"treegrid-CPU\">";
                html+="<th>CPU</th>";
                html+="<td>"+getTranslationString('119',false)+":</td>";	// Number of processors
                html+="<td class=\"rightCell\"><span></span></td>";
                html+="</tr>";
            }
            html+="<tr id=\"hardware-CPU-" + i +"\" class=\"treegrid-CPU-" + i +" treegrid-parent-CPU\">";
            html+="<th></th>";
            html+="<td><span data-bind=\"Model\"></span></td>";
            html+="<td></td>";
            html+="</tr>";
            for (var proc_param in paramlist) {
                if (datas[i]["@attributes"][proc_param] !== undefined) {
                    html+="<tr id=\"hardware-CPU-" + i + "-" + proc_param + "\" class=\"treegrid-parent-CPU-" + i +"\">";
                    html+="<th></th>";
                    html+="<td>"+getTranslationString(paramlist[proc_param],false)+"</td>";
                    html+="<td class=\"rightCell\"><span data-bind=\"" + proc_param + "\"></span></td>";
                    html+="</tr>"; 
                }
            }

        }
    }
    catch (err) {
        $("#hardware-CPU").hide();
    }

    for (hw_type in {PCI:0,IDE:1,SCSI:2,USB:3,TB:4,I2C:5}) {
        try {
            var datas = items(data["Hardware"][hw_type]["Device"]);
            for (var i = 0; i < datas.length; i++) {
                if (i == 0) {
                    html+="<tr id=\"hardware-" + hw_type + "\" class=\"treegrid-" + hw_type + "\">";
                    html+="<th>" + hw_type + "</th>";
                    html+="<td>"+getTranslationString('120',false)+":</td>"; //Number of devices
                    html+="<td class=\"rightCell\"><span></span></td>";
                    html+="</tr>";
                }
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
    $("#hardware").empty().append(html);


    if ((data["Hardware"]["@attributes"] !== undefined) && (data["Hardware"]["@attributes"]["Name"] !== undefined)) {
        $('#hardware-Machine').render(data["Hardware"]["@attributes"]);
    }

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

    for (hw_type in {PCI:0,IDE:1,SCSI:2,USB:3,TB:4,I2C:5}) {
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
        try {
            $('#hardware-CPU').treegrid('expand');
        }
        catch (err) {
        }
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
                    var rest = parseInt(this["@attributes"]["Percent"]);
                    var html = '<div class="progress">';
                    if ((this["Details"]["@attributes"]["AppPercent"] !== undefined) && (this["Details"]["@attributes"]["AppPercent"] > 0)) {
                        html += '<div class="progress-bar progress-bar-info" style="width: ' + this["Details"]["@attributes"]["AppPercent"] + '%;"></div>';
                        rest -= parseInt(this["Details"]["@attributes"]["AppPercent"]);
                    }
                    if ((this["Details"]["@attributes"]["CachedPercent"] !== undefined) && (this["Details"]["@attributes"]["CachedPercent"] > 0)) {
                        html += '<div class="progress-bar progress-bar-warning" style="width: ' + this["Details"]["@attributes"]["CachedPercent"] + '%;"></div>';
                        rest -= parseInt(this["Details"]["@attributes"]["CachedPercent"]);
                    }
                    if ((this["Details"]["@attributes"]["BuffersPercent"] !== undefined) && (this["Details"]["@attributes"]["BuffersPercent"] > 0)) {
                        html += '<div class="progress-bar progress-bar-danger" style="width: ' + this["Details"]["@attributes"]["BuffersPercent"] + '%;"></div>';
                        rest -= parseInt(this["Details"]["@attributes"]["BuffersPercent"]);
                    }
                    if (rest > 0) {
                        html += '<div class="progress-bar progress-bar-success" style="width: ' + rest + '%;"></div>';
                    }
                    html += '</div>';
                    html += '<div class="percent">' + 'Total: ' + this["@attributes"]["Percent"] + '% ' + '<i>(';
                    var not_first = false;
                    if (this["Details"]["@attributes"]["AppPercent"] !== undefined) {
                        html += getTranslationString('064',false)+': '+ this["Details"]["@attributes"]["AppPercent"] + '%'; 		// Kernel + apps
                        not_first = true;
                    }
                    if (this["Details"]["@attributes"]["CachedPercent"] !== undefined) {
                        if (not_first) html += ' - ';
                        html += getTranslationString('066',false)+': ' + this["Details"]["@attributes"]["CachedPercent"] + '%'; 	// Cache
                        not_first = true;
                    }
                    if (this["Details"]["@attributes"]["BuffersPercent"] !== undefined) {
                        if (not_first) html += ' - ';
                        html += getTranslationString('065',false)+': ' + this["Details"]["@attributes"]["BuffersPercent"] + '%';	//Buffers
                    }
                    html += ')</i></div>';
                    return html;
                }
            }
        },
        Type: {
            text: function () {
                return getTranslationString('028',false); //"Physical Memory";
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
                return this['Name'] + '<br/>' + ((this["MountPoint"] !== undefined) ? this["MountPoint"] : this["MountPointID"]);
            }
        }
    };

    var data_memory = [];
    if (data["Memory"]["Swap"] !== undefined) { 
        var datas = items(data["Memory"]["Swap"]["Mount"]);
        data_memory.push_attrs(datas);
        $('#swap-data').render(data_memory, directive_swap);
        $('#swap-data').show();
    } else {
        $('#swap-data').hide();
    }
    $('#memory-data').render(data["Memory"], directives);
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
        var total = {Total:0,Free:0,Used:0};
        for (var i = 0; i < datas.length; i++) {
            fs_data.push(datas[i]["@attributes"]);
            total["Total"] += parseInt(datas[i]["@attributes"]["Total"]);
            total["Free"] += parseInt(datas[i]["@attributes"]["Free"]);
            total["Used"] += parseInt(datas[i]["@attributes"]["Used"]);
            total["Percent"] = (total["Total"] !== 0) ? round((total["Used"] / total["Total"]) * 100, 2) : 0;
        }
        if (i > 0) {
            $('#filesystem-data').render(fs_data, directives);
            $('#filesystem-foot').render(total, directives);
            $('#filesystem_MountPoint').removeClass("sorttable_sorted"); // reset sort order
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
                return this["Err"] + "/" + String.fromCharCode(8203) + this["Drops"];
            }
        }
    };

    var html = "";

    html+="<thead>";
    html+="<tr>";
    html+="<th width=\"60%\">"+getTranslationString('022',false)+"</th>"; // Device
    html+="<th class=\"rightCell\">"+getTranslationString('023',false)+"</th>"; // Receive
    html+="<th class=\"rightCell\">"+getTranslationString('024',false)+"</th>"; // Sent
    html+="<th class=\"rightCell\">"+getTranslationString('025',false)+"</th>"; // Drop //thema
    html+="</tr>";
    html+="</thead>";

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
                var infos = info.replace(/:/g, String.fromCharCode(8203)+":").split(";"); /* split long addresses */
                for (var j = 0; j < infos.length; j++){
                    html +="<tr class=\"treegrid-parent-network-" + i + "\"><td>" + infos[j] + "</td><td></td><td></td><td></td></tr>";
                }
            }
        }
        $("#network").empty().append(html);
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
        if (voltage_data.push_attrs(datas) > 0) {
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
            html: function () {
                return formatTemp(this["Value"], data["Options"]["@attributes"]["tempFormat"]);
            }
        },
        Max: {
            html: function () {
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
        if (temperature_data.push_attrs(datas) > 0) {
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
                return this["Value"] + String.fromCharCode(160) + getTranslationString('063',false); //"RPM";
            }
        },
        Min: {
            text: function () {
                if (this["Min"] !== undefined)
                    return this["Min"] + String.fromCharCode(160) + getTranslationString('063',false); // "RPM";
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
        if (fans_data.push_attrs(datas) > 0) {
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
        if (power_data.push_attrs(datas) > 0) {
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
        if (current_data.push_attrs(datas) > 0) {
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
                return this["LineVoltage"] + String.fromCharCode(160) + getTranslationString('082',false); //"V";
            }
        },
        LineFrequency: {
            text: function () {
                return this["LineFrequency"] + String.fromCharCode(160) + getTranslationString('109',false); //"Hz";
            }
        },
        BatteryVoltage: {
            text: function () {
                return this["BatteryVoltage"] + String.fromCharCode(160) + getTranslationString('082',false); //"V";
            }
        },
        TimeLeftMinutes: {
            text: function () {
                return this["TimeLeftMinutes"] + String.fromCharCode(160) + getTranslationString('083',false); //"minutes";
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

    if ((data["UPSInfo"] !== undefined) && (items(data["UPSInfo"]["UPS"]).length > 0)) {
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
    
        $("#ups").empty().append(html);

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
    } else {
        $("#block_ups").hide();
    }
}

function renderErrors(data) {
    try {
        var datas = items(data["Errors"]["Error"]);
        for (var i = 0; i < datas.length; i++) { 
            $("#errors").append("<li><b>"+datas[i]["@attributes"]["Function"]+"</b> - "+datas[i]["@attributes"]["Message"].replace(/\n/g, "<br>")+"</li><br>");
        }
        if (i > 0) {
            $("#errorbutton").show();
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
    if (intDays) {		// days
        txt += intDays.toString() + String.fromCharCode(160) + getTranslationString('048',false) + String.fromCharCode(160);
    }
    if (intHours) {		// hours
        txt += intHours.toString() + String.fromCharCode(160) + getTranslationString('049',false) + String.fromCharCode(160);
    }					// Minutes
    return txt + intMin.toString() + String.fromCharCode(160) + getTranslationString('050',false);
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
        show += String.fromCharCode(160) + "kB";
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
                            show += String.fromCharCode(160) + "kB";
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
