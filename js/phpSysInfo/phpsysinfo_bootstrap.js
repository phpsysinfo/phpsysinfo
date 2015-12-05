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
var langxml = [], langcounter = 1, langarr = [], current_language = "", plugins = []; plugin_liste = [];

/**
 * generate a cookie, if not exist, and add an entry to it<br><br>
 * inspired by <a href="http://www.quirksmode.org/js/cookies.html">http://www.quirksmode.org/js/cookies.html</a>
 * @param {String} name name that holds the value
 * @param {String} value value that needs to be stored
 * @param {Number} days how many days the entry should be valid in the cookie
 */
function createCookie(name, value, days) {
    var date = new Date(), expires = "";
    if (days) {
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        if (typeof(date.toUTCString)==="function") {
            expires = "; expires=" + date.toUTCString();
        } else {
            //deprecated
            expires = "; expires=" + date.toGMTString();
        }
    }
    else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

/**
 * read a value out of a cookie and return the value<br><br>
 * inspired by <a href="http://www.quirksmode.org/js/cookies.html">http://www.quirksmode.org/js/cookies.html</a>
 * @param {String} name name of the value that should be retrieved
 * @return {String}
 */
function readCookie(name) {
    var nameEQ = "", ca = [], c = '';
    nameEQ = name + "=";
    ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i += 1) {
        c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1, c.length);
        }
        if (!c.indexOf(nameEQ)) {
            return c.substring(nameEQ.length, c.length);
        }
    }
    return null;
}

/**
 * activates a given style and disables the old one in the document
 * @param {String} template template that should be activated
 */
function switchStyle(template) {
    $('link[rel*=style][title]').each(function getTitle(i) {
        if (this.getAttribute('title') === 'PSI_Template') {
            this.setAttribute('href', './templates/' + template + "_bootstrap.css");
        }
    });
}

/**
 * load the given translation an translate the entire page<br><br>retrieving the translation is done through a
 * ajax call
 * @private
 * @param {String} plugin if plugin is given, the plugin translation file will be read instead of the main translation file
 * @param {String} plugname internal plugin name
 * @return {jQuery} translation jQuery-Object
 */
function getLanguage(plugin, plugname) {
    var getLangUrl = "";
    if (current_language) {
        getLangUrl = 'language/language.php?lang=' + current_language;
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
                langarr[plugname][this.getAttribute('id')] = $("exp", idexp).text().toString().replace(/\//g, "/<wbr>");
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
    var plugname = current_language + "_";
    if (plugin === undefined) {
        plugname += "phpSysInfo";
    }
    else {
        plugname += plugin;
    }
    if (langxml[plugname] === undefined) {
        langxml.push(plugname);
        getLanguage(plugin, plugname);
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
function genlang(id, generate, plugin) {
    var html = "", idString = "", plugname = "";
    if (plugin === undefined) {
        plugname = "";
    }
    else {
        plugname = plugin.toLowerCase();
    }
    if (id < 100) {
        if (id < 10) {
            idString = "00" + id.toString();
        }
        else {
            idString = "0" + id.toString();
        }
    }
    else {
        idString = id.toString();
    }
    if (plugin) {
        idString = "plugin_" + plugname + "_" + idString;
    }
    if (generate) {
        html += "<span id=\"lang_" + idString + "-" + langcounter.toString() + "\">";
        langcounter += 1;
    }
    else {
        html += "<span id=\"lang_" + idString + "\">";
    }
    html += getTranslationString(idString, plugin) + "</span>";
    return html;
}

/**
 * translates all expressions based on the translation xml file<br>
 * translation expressions must be in the format &lt;span id="lang???"&gt;&lt;/span&gt;, where ??? is
 * the number of the translated expression in the xml file<br><br>if a translated expression is not found in the xml
 * file nothing would be translated, so the initial value which is inside the span tag is displayed
 * @param {String} [plugin] name of the plugin
 */
function changeLanguage(plugin) {
    var langId = "", langStr = "";
    $('span[id*=lang_]').each(function translate(i) {
        langId = this.getAttribute('id').substring(5);
        if (langId.indexOf('-') !== -1) {
            langId = langId.substring(0, langId.indexOf('-')); //remove the unique identifier
        }
        langStr = getTranslationString(langId, plugin);
        if (langStr !== undefined) {
            if (langStr.length > 0) {
                this.innerHTML = langStr;
            }
        }
    });
}
function reload(initiate) {
    $("#errorbutton").css("visibility", "hidden");
    $("#errors").empty();
    $.ajax({
        dataType: "json",
        url: "xml.php?json",
        error: function(jqXHR, status, thrownError) {;
            if ((status === "parsererror") && (typeof(xmlDoc = $.parseXML(jqXHR.responseText)) === "object")) {
                var errs = 0;
                try {
                    $(xmlDoc).find("Error").each(function() {
                        $("#errors").append("<li><b>"+$(this)[0]["attributes"]["Function"].nodeValue+"</b> - "+$(this)[0]["attributes"]["Message"].nodeValue.replace(/\n/g, "<br>")+"</li><br>");
                        errs++;
                    });
                }
                catch (err) {
                }
                if (errs > 0) {
                    $("#errorbutton").css("visibility", "visible");
                    $("#output").show();
                }
            }
        },
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
            $("#select").show();
            $("#output").show();
        }
    });

    for (var i = 0; i < plugins.length; i++) {
        $.ajax({
             dataType: "json",
             url: "xml.php?plugin=" + plugins[i] + "&json",
             pluginname: plugins[i],
             success: function (data) {
                try {
                    // dynamic call
                    window['renderPlugin_' + this.pluginname](data);
                    changeLanguage(this.pluginname);
                    plugin_liste.pushIfNotExist(this.pluginname);
                }
                catch (err) {
                }
                renderErrors(data);
            }
        });
    }
}

$(document).ready(function () {
    var cookie_template = null, cookie_language = null, plugtmp = "";

    $(document).ajaxStart(function () {
        $("#loader").css("visibility", "visible");
    });
    $(document).ajaxStop(function () {
        $("#loader").css("visibility", "hidden");
    });

    sorttable.init();

    $.getScript( "./js.php?name=bootstrap", function(data, status, jqxhr) {

        plugtmp = $("#plugins").val().toString();
        if (plugtmp.length >0 ){
            plugins = plugtmp.split(',');
        }

        if ($("#language option").size() < 2) {
            current_language = $("#language").val().toString();
/* not visible any objects
            changeLanguage();
*/
/* plugin_liste not initialized yet
            for (var i = 0; i < plugin_liste.length; i += 1) {
                changeLanguage(plugin_liste[i]);
            }
*/
        } else {
            cookie_language = readCookie("psi_language");
            if (cookie_language !== null) {
                current_language = cookie_language;
                $("#language").val(current_language);
            } else {
                current_language = $("#language").val().toString();
            }
/* not visible any objects
            changeLanguage();
*/
/* plugin_liste not initialized yet
            for (var i = 0; i < plugin_liste.length; i += 1) {
                changeLanguage(plugin_liste[i]);
            }
*/
            $('#language').show();
            $('span[id=lang_045]').show(); 
            $("#language").change(function changeLang() {
                current_language = $("#language").val().toString();
                createCookie('psi_language', current_language, 365);
                changeLanguage();
                for (var i = 0; i < plugin_liste.length; i++) {
                    changeLanguage(plugin_liste[i]);
                }
                return false;
            });
        }
        if ($("#template option").size() < 2) {
            switchStyle($("#template").val().toString());
        } else {
            cookie_template = readCookie("psi_bootstrap_template");
            if (cookie_template !== null) {
                $("#template").val(cookie_template);
            }
            switchStyle($("#template").val().toString());
            $('#template').show();
            $('span[id=lang_044]').show();
            $("#template").change(function changeTemplate() {
                switchStyle($("#template").val().toString());
                createCookie('psi_bootstrap_template', $("#template").val().toString(), 365);
                return false;
            });
        }

        reload(true);

        $(".logo").click(function () {
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
            html: function () {
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
                return '<img src="gfx/images/' + this["Distroicon"] + '" style="width:32px;"/>' + " " +this["Distro"];
            }
        },
        LoadAvg: {
            html: function () {
                if (this["CPULoad"] !== undefined) {
                    return '<table style="width:100%;"><tr><td style="width:50%;">'+this["LoadAvg"] + '</td><td><div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width:' + round(this["CPULoad"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["CPULoad"],0) + '%</div></td></tr></table>';
                } else {
                    return this["LoadAvg"];
                }
            }
        },
        Processes: {
            html: function () {
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
                    for (var proc_type in {111:0,112:1,113:2,114:3,115:4,116:5}) {
                        if (eval("p" + proc_type)) {
                            if (not_first) {
                                processes += ", ";
                            }
                            processes += eval("p" + proc_type) + String.fromCharCode(160) + genlang(proc_type, false);
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
    $("#block_vitals").show();
}

function renderHardware(data) {

    var directives = {
        Model: {
            text: function () {
                return this["Model"];
            }
        },
        CpuSpeed: {
            html: function () {
                return formatHertz(this["CpuSpeed"]);
            }
        },
        CpuSpeedMax: {
            html: function () {
                return formatHertz(this["CpuSpeedMax"]);
            }
        },
        CpuSpeedMin: {
            html: function () {
                return formatHertz(this["CpuSpeedMin"]);
            }
        },
        Cache: {
            html: function () {
                return formatBytes(this["Cache"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        BusSpeed: {
            html: function () {
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
                        '<div class="progress-bar progress-bar-info" style="width:' + round(this["Load"],0) + '%;"></div>' +
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
        html+="<th style=\"width:8%;\">"+genlang(107, false)+"</th>"; //Machine
        html+="<td><span data-bind=\"Name\"></span></td>";
        html+="<td></td>";
        html+="</tr>";
    }

    var paramlist = {CpuSpeed:13,CpuSpeedMax:100,CpuSpeedMin:101,Cache:15,Virt:94,BusSpeed:14,Bogomips:16,Cputemp:51,Load:9};
    try {
        var datas = items(data["Hardware"]["CPU"]["CpuCore"]);
        for (var i = 0; i < datas.length; i++) {
             if (i == 0) {
                html+="<tr id=\"hardware-CPU\" class=\"treegrid-CPU\">";
                html+="<th>CPU</th>";
                html+="<td><span class=\"treegrid-span\">" + genlang(119, false) + ":</span></td>"; //Number of processors
                html+="<td class=\"rightCell\"><span id=\"CPUCount\"></span></td>";
                html+="</tr>";
            }
            html+="<tr id=\"hardware-CPU-" + i +"\" class=\"treegrid-CPU-" + i +" treegrid-parent-CPU\">";
            html+="<th></th>";
            html+="<td><span class=\"treegrid-span\" data-bind=\"Model\"></span></td>";
            html+="<td></td>";
            html+="</tr>";
            for (var proc_param in paramlist) {
                if (datas[i]["@attributes"][proc_param] !== undefined) {
                    html+="<tr id=\"hardware-CPU-" + i + "-" + proc_param + "\" class=\"treegrid-parent-CPU-" + i +"\">";
                    html+="<th></th>";
                    html+="<td><span class=\"treegrid-span\">" + genlang(paramlist[proc_param], true) + "<span></td>";
                    html+="<td class=\"rightCell\"><span data-bind=\"" + proc_param + "\"></span></td>";
                    html+="</tr>";
                }
            }

        }
    }
    catch (err) {
        $("#hardware-CPU").hide();
    }

    for (var hw_type in {PCI:0,IDE:1,SCSI:2,USB:3,TB:4,I2C:5}) {
        try {
            var datas = items(data["Hardware"][hw_type]["Device"]);
            for (var i = 0; i < datas.length; i++) {
                if (i == 0) {
                    html+="<tr id=\"hardware-" + hw_type + "\" class=\"treegrid-" + hw_type + "\">";
                    html+="<th>" + hw_type + "</th>";
                    html+="<td><span class=\"treegrid-span\">" + genlang('120', false) + ":</span></td>"; //Number of devices
                    html+="<td class=\"rightCell\"><span id=\"" + hw_type + "Count\"></span></td>";
                    html+="</tr>";
                }
                html+="<tr id=\"hardware-" + hw_type + "-" + i +"\" class=\"treegrid-parent-" + hw_type + "\">";
                html+="<th></th>";
                html+="<td><span class=\"treegrid-span\" data-bind=\"hwName\"></span></td>";
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
            $("#CPUCount").html(i);
        }
    }
    catch (err) {
        $("#hardware-CPU").hide();
    }

    for (var hw_type in {PCI:0,IDE:1,SCSI:2,USB:3,TB:4,I2C:5}) {
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
                $("#" + hw_type + "Count").html(licz);
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
    $("#block_hardware").show();
}

function renderMemory(data) {
    var directives = {
        Total: {
            html: function () {
                return formatBytes(this["@attributes"]["Total"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Free: {
            html: function () {
                return formatBytes(this["@attributes"]["Free"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Used: {
            html: function () {
                return formatBytes(this["@attributes"]["Used"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Usage: {
            html: function () {
                if ((this["Details"] === undefined) || (this["Details"]["@attributes"] === undefined)) {
                    return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width:' + this["@attributes"]["Percent"] + '%;"></div>' +
                        '</div><div class="percent">' + this["@attributes"]["Percent"] + '%</div>';
                }
                else {
                    var rest = parseInt(this["@attributes"]["Percent"]);
                    var html = '<div class="progress">';
                    if ((this["Details"]["@attributes"]["AppPercent"] !== undefined) && (this["Details"]["@attributes"]["AppPercent"] > 0)) {
                        html += '<div class="progress-bar progress-bar-info" style="width:' + this["Details"]["@attributes"]["AppPercent"] + '%;"></div>';
                        rest -= parseInt(this["Details"]["@attributes"]["AppPercent"]);
                    }
                    if ((this["Details"]["@attributes"]["CachedPercent"] !== undefined) && (this["Details"]["@attributes"]["CachedPercent"] > 0)) {
                        html += '<div class="progress-bar progress-bar-warning" style="width:' + this["Details"]["@attributes"]["CachedPercent"] + '%;"></div>';
                        rest -= parseInt(this["Details"]["@attributes"]["CachedPercent"]);
                    }
                    if ((this["Details"]["@attributes"]["BuffersPercent"] !== undefined) && (this["Details"]["@attributes"]["BuffersPercent"] > 0)) {
                        html += '<div class="progress-bar progress-bar-danger" style="width:' + this["Details"]["@attributes"]["BuffersPercent"] + '%;"></div>';
                        rest -= parseInt(this["Details"]["@attributes"]["BuffersPercent"]);
                    }
                    if (rest > 0) {
                        html += '<div class="progress-bar progress-bar-success" style="width:' + rest + '%;"></div>';
                    }
                    html += '</div>';
                    html += '<div class="percent">' + 'Total: ' + this["@attributes"]["Percent"] + '% ' + '<i>(';
                    var not_first = false;
                    if (this["Details"]["@attributes"]["AppPercent"] !== undefined) {
                        html += genlang(64, false) + ': '+ this["Details"]["@attributes"]["AppPercent"] + '%'; //Kernel + apps
                        not_first = true;
                    }
                    if (this["Details"]["@attributes"]["CachedPercent"] !== undefined) {
                        if (not_first) html += ' - ';
                        html += genlang(66, false) + ': ' + this["Details"]["@attributes"]["CachedPercent"] + '%'; //Cache
                        not_first = true;
                    }
                    if (this["Details"]["@attributes"]["BuffersPercent"] !== undefined) {
                        if (not_first) html += ' - ';
                        html += genlang(65, false) + ': ' + this["Details"]["@attributes"]["BuffersPercent"] + '%'; //Buffers
                    }
                    html += ')</i></div>';
                    return html;
                }
            }
        },
        Type: {
            html: function () {
                return genlang(28, false); //Physical Memory
            }
        }
    };

    var directive_swap = {
        Total: {
            html: function () {
                return formatBytes(this["Total"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Free: {
            html: function () {
                return formatBytes(this["Free"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Used: {
            html: function () {
                return formatBytes(this["Used"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Usage: {
            html: function () {
                return '<div class="progress">' +
                    '<div class="progress-bar progress-bar-info" style="width:' + this["Percent"] + '%;"></div>' +
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
    $("#block_memory").show();
}

function renderFilesystem(data) {
    var directives = {
        Total: {
            html: function () {
                return formatBytes(this["Total"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Free: {
            html: function () {
                return formatBytes(this["Free"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Used: {
            html: function () {
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
                    '" style="width:' + this["Percent"] + '% ;"></div>' +
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
            $('#filesystem_MountPoint').removeClass("sorttable_sorted"); //reset sort order
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
            html: function () {
                return formatBytes(this["RxBytes"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        TxBytes: {
            html: function () {
                return formatBytes(this["TxBytes"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        Drops: {
            html: function () {
                return this["Err"] + "/<wbr>" + this["Drops"];
            }
        }
    };

    var html = "";

    try {
        var network_data = [];
        var datas = items(data["Network"]["NetDevice"]);
        for (var i = 0; i < datas.length; i++) {
            html+="<tr id=\"network-" + i +"\" class=\"treegrid-network-" + i + "\">";
            html+="<td><span class=\"treegrid-spanbold\" data-bind=\"Name\"></span></td>";
            html+="<td class=\"rightCell\"><span data-bind=\"RxBytes\"></span></td>";
            html+="<td class=\"rightCell\"><span data-bind=\"TxBytes\"></span></td>";
            html+="<td class=\"rightCell\"><span data-bind=\"Drops\"></span></td>";
            html+="</tr>";

            var info  = datas[i]["@attributes"]["Info"];
            if ( (info !== undefined) && (info !== "") ) {
                var infos = info.replace(/:/g, "<wbr>:").split(";"); /* split long addresses */
                for (var j = 0; j < infos.length; j++){
                    html +="<tr class=\"treegrid-parent-network-" + i + "\"><td><span class=\"treegrid-span\">" + infos[j] + "</span></td><td></td><td></td><td></td></tr>";
                }
            }
        }
        $("#network-data").empty().append(html);
        if (i > 0) {
            for (var i = 0; i < datas.length; i++) {
                $('#network-' + i).render(datas[i]["@attributes"], directives);
            }
            $('#network').treegrid({
                initialState: 'collapsed',
                expanderExpandedClass: 'normalicon normalicon-down',
                expanderCollapsedClass: 'normalicon normalicon-right'
            });
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
            html: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " <img style=\"vertical-align: middle; width:20px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\"" + this["Event"] + "\"/>";
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
            html: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " <img style=\"vertical-align: middle; width:20px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\"" + this["Event"] + "\"/>";
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
            html: function () {
                return this["Value"] + String.fromCharCode(160) + genlang(63, true); //RPM
            }
        },
        Min: {
            html: function () {
                if (this["Min"] !== undefined)
                    return this["Min"] + String.fromCharCode(160) + genlang(63, true); //RPM
            }
        },
        Label: {
            html: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " <img style=\"vertical-align: middle; width:20px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\"" + this["Event"] + "\"/>";
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
            html: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " <img style=\"vertical-align: middle; width:20px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\"" + this["Event"] + "\"/>";
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
            html: function () {
                if (this["Event"] === undefined)
                    return this["Label"];
                else
                    return this["Label"] + " <img style=\"vertical-align: middle; width:20px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\"" + this["Event"] + "\"/>";
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
            html: function () {
                return this["LineVoltage"] + String.fromCharCode(160) + genlang(82, true); //V
            }
        },
        LineFrequency: {
            html: function () {
                return this["LineFrequency"] + String.fromCharCode(160)  + genlang(109, false); //Hz
            }
        },
        BatteryVoltage: {
            html: function () {
                return this["BatteryVoltage"] + String.fromCharCode(160) + genlang(82, true);; //V
            }
        },
        TimeLeftMinutes: {
            html: function () {
                return this["TimeLeftMinutes"] + String.fromCharCode(160) + genlang(83, false); //minutes
            }
        },
        LoadPercent: {
            html: function () {
                return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width:' + round(this["LoadPercent"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["LoadPercent"],0) + '%</div>';
            }
        },
        BatteryChargePercent: {
            html: function () {
                return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width:' + round(this["BatteryChargePercent"],0) + '%;"></div>' +
                        '</div><div class="percent">' + round(this["BatteryChargePercent"],0) + '%</div>';
            }
        }
    };

    if ((data["UPSInfo"] !== undefined) && (items(data["UPSInfo"]["UPS"]).length > 0)) {
        var html="";
        var paramlist = {Model:70,StartTime:72,Status:73,Temperature:84,OutagesCount:74,LastOutage:75,LastOutageFinish:76,LineVoltage:77,LineFrequency:108,LoadPercent:78,BatteryDate:104,BatteryVoltage:79,BatteryChargePercent:80,TimeLeftMinutes:81};

        try {
            var datas = items(data["UPSInfo"]["UPS"]);
            for (var i = 0; i < datas.length; i++) {
                html+="<tr id=\"ups-" + i +"\" class=\"treegrid-UPS-" + i+ "\">";
                html+="<td style=\"width:60%;\"><span class=\"treegrid-spanbold\" data-bind=\"Name\"></span></td>";
                html+="<td></td>";
                html+="</tr>";
                for (var proc_param in paramlist) {
                    if (datas[i]["@attributes"][proc_param] !== undefined) {
                        html+="<tr id=\"ups-" + i + "-" + proc_param + "\" class=\"treegrid-parent-UPS-" + i +"\">";
                        html+="<td><span class=\"treegrid-spanbold\">" + genlang(paramlist[proc_param], true) + "</span></td>";
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
            $("#errorbutton").css("visibility", "visible");
        }
    }
    catch (err) {
        $("#errorbutton").css("visibility", "hidden");
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
        txt += intDays.toString() + String.fromCharCode(160) + genlang(48, false) + String.fromCharCode(160); //days
    }
    if (intHours) {
        txt += intHours.toString() + String.fromCharCode(160) + genlang(49, false) + String.fromCharCode(160); //hours
    }
    return txt + intMin.toString() + String.fromCharCode(160) + genlang(50, false); //Minutes
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
            return round((((9 * degree) / 5) + 32), 1) + String.fromCharCode(160) + genlang(61, true);
        case "c":
            return round(degree, 1) + String.fromCharCode(160) + genlang(60, true);
        case "c-f":
            return round(degree, 1) + String.fromCharCode(160) + genlang(60, true) + "<br><i>(" + round((((9 * degree) / 5) + 32), 1) + String.fromCharCode(160) + genlang(61, true) + ")</i>";
        case "f-c":
            return round((((9 * degree) / 5) + 32), 1) + String.fromCharCode(160) + genlang(61, true) + "<br><i>(" + round(degree, 1) + String.fromCharCode(160) + genlang(60, true) + ")</i>";
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
        return mhertz.toString() + String.fromCharCode(160) + genlang(92, true);
    }
    else {
        if (mhertz && mhertz >= 1000) {
            return round(mhertz / 1000, 2) + String.fromCharCode(160) + genlang(93, true);
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
        show += String.fromCharCode(160) + genlang(90, true);
        break;
    case "tib":
        show += round(bytes / Math.pow(1024, 4), 2);
        show += String.fromCharCode(160) + genlang(86, true);
        break;
    case "gib":
        show += round(bytes / Math.pow(1024, 3), 2);
        show += String.fromCharCode(160) + genlang(87, true);
        break;
    case "mib":
        show += round(bytes / Math.pow(1024, 2), 2);
        show += String.fromCharCode(160) + genlang(88, true);
        break;
    case "kib":
        show += round(bytes / Math.pow(1024, 1), 2);
        show += String.fromCharCode(160) + genlang(89, true);
        break;
    case "pb":
        show += round(bytes / Math.pow(1000, 5), 2);
        show += String.fromCharCode(160) + genlang(91, true);
        break;
    case "tb":
        show += round(bytes / Math.pow(1000, 4), 2);
        show += String.fromCharCode(160) + genlang(85, true);
        break;
    case "gb":
        show += round(bytes / Math.pow(1000, 3), 2);
        show += String.fromCharCode(160) + genlang(41, true);
        break;
    case "mb":
        show += round(bytes / Math.pow(1000, 2), 2);
        show += String.fromCharCode(160) + genlang(40, true);
        break;
    case "kb":
        show += round(bytes / Math.pow(1000, 1), 2);
        show += String.fromCharCode(160) + genlang(39, true);
        break;
    case "b":
        show += bytes;
        show += String.fromCharCode(160) + genlang(96, true);
        break;
    case "auto_decimal":
        if (bytes > Math.pow(1000, 5)) {
            show += round(bytes / Math.pow(1000, 5), 2);
            show += String.fromCharCode(160) + genlang(91, true);
        }
        else {
            if (bytes > Math.pow(1000, 4)) {
                show += round(bytes / Math.pow(1000, 4), 2);
                show += String.fromCharCode(160) + genlang(85, true);
            }
            else {
                if (bytes > Math.pow(1000, 3)) {
                    show += round(bytes / Math.pow(1000, 3), 2);
                    show += String.fromCharCode(160) + genlang(41, true);
                }
                else {
                    if (bytes > Math.pow(1000, 2)) {
                        show += round(bytes / Math.pow(1000, 2), 2);
                        show += String.fromCharCode(160) + genlang(40, true);
                    }
                    else {
                        if (bytes > Math.pow(1000, 1)) {
                            show += round(bytes / Math.pow(1000, 1), 2);
                            show += String.fromCharCode(160) + genlang(39, true);
                        }
                        else {
                                show += bytes;
                                show += String.fromCharCode(160) + genlang(96, true);
                        }
                    }
                }
            }
        }
        break;
    default:
        if (bytes > Math.pow(1024, 5)) {
            show += round(bytes / Math.pow(1024, 5), 2);
            show += String.fromCharCode(160) + genlang(90, true);
        }
        else {
            if (bytes > Math.pow(1024, 4)) {
                show += round(bytes / Math.pow(1024, 4), 2);
                show += String.fromCharCode(160) + genlang(86, true);
            }
            else {
                if (bytes > Math.pow(1024, 3)) {
                    show += round(bytes / Math.pow(1024, 3), 2);
                    show += String.fromCharCode(160) + genlang(87, true);
                }
                else {
                    if (bytes > Math.pow(1024, 2)) {
                        show += round(bytes / Math.pow(1024, 2), 2);
                        show += String.fromCharCode(160) + genlang(88, true);
                    }
                    else {
                        if (bytes > Math.pow(1024, 1)) {
                            show += round(bytes / Math.pow(1024, 1), 2);
                            show += String.fromCharCode(160) + genlang(89, true);
                        }
                        else {
                            show += bytes;
                            show += String.fromCharCode(160) + genlang(96, true);
                        }
                    }
                }
            }
        }
    }
    return show;
}

Array.prototype.pushIfNotExist = function(val) {
    if (typeof(val) == 'undefined' || val == '') {
        return;
    }
    val = $.trim(val);
    if ($.inArray(val, this) == -1) {
        this.push(val);
    }
};

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
