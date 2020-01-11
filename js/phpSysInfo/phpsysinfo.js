/***************************************************************************
 *   Copyright (C) 2008 by phpSysInfo - A PHP System Information Script    *
 *   http://phpsysinfo.sourceforge.net/                                    *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
//
// $Id: phpsysinfo.js 699 2012-09-15 11:57:13Z namiltd $
//

/*global $, jQuery */

"use strict";

var langxml = [], filesystemTable, current_language = "", plugin_liste = [], blocks = [], langarr = [],
     showCPUListExpanded, showCPUInfoExpanded, showNetworkInfosExpanded, showMemoryInfosExpanded, showNetworkActiveSpeed, showCPULoadCompact, oldnetwork = [];

/**
 * Fix PNG loading on IE6 or below
 */
function PNGload(png) {
    if (typeof(png.ifixpng)==='function') { //IE6 PNG fix
        png.ifixpng('./gfx/blank.gif');
    }
}

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
    } else {
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
    var nameEQ = "", ca = [], c = '', i = 0;
    nameEQ = name + "=";
    ca = document.cookie.split(';');
    for (i = 0; i < ca.length; i++) {
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

/**
 * activates a given style and disables the old one in the document
 * @param {String} template template that should be activated
 */
function switchStyle(template) {
    $('link[rel*=style][title]').each(function getTitle(i) {
        if (this.getAttribute('title') === 'PSI_Template') {
            this.setAttribute('href', './templates/' + template + ".css");
        }
    });
}

/**
 * load the given translation an translate the entire page<br><br>retrieving the translation is done through a
 * ajax call
 * @private
 * @param {String} plugin if plugin is given, the plugin translation file will be read instead of the main translation file
 * @param {String} langarrId internal plugin name
 * @return {jQuery} translation jQuery-Object
 */
function getLanguage(plugin, langarrId) {
    var getLangUrl = "";
    if (current_language) {
        getLangUrl = 'language/language.php?lang=' + current_language;
        if (plugin) {
            getLangUrl += "&plugin=" + plugin;
        }
    } else {
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
        error: function error() {
            $.jGrowl("Error loading language - " + getLangUrl);
        },
        success: function buildblocks(xml) {
            var idexp;
            langxml[langarrId] = xml;
            if (langarr[langarrId] === undefined) {
                langarr.push(langarrId);
                langarr[langarrId] = [];
            }
            $("expression", langxml[langarrId]).each(function langstore(id) {
                idexp = $("expression", xml).get(id);
                langarr[langarrId][this.getAttribute('id')] = $("exp", idexp).text().toString().replace(/\//g, "/<wbr>");
            });
            changeSpanLanguage(plugin);
        }
    });
}

/**
 * generate a span tag
 * @param {Number} id translation id in the xml file
 * @param {String} [plugin] name of the plugin for which the tag should be generated
 * @return {String} string which contains generated span tag for translation string
 */
function genlang(id, plugin) {
    var html = "", idString = "", plugname = "",
        langarrId = current_language + "_";

    if (plugin === undefined) {
        plugname = "";
        langarrId += "phpSysInfo";
    } else {
        plugname = plugin.toLowerCase();
        langarrId += plugname;
    }
    if (id < 100) {
        if (id < 10) {
            idString = "00" + id.toString();
        } else {
            idString = "0" + id.toString();
        }
    } else {
        idString = id.toString();
    }
    if (plugin) {
        idString = "plugin_" + plugname + "_" + idString;
    }

    html += "<span class=\"lang_" + idString + "\">";

    if ((langxml[langarrId] !== undefined) && (langarr[langarrId] !== undefined)) {
        html += langarr[langarrId][idString];
    }    

    html += "</span>";

    return html;
}

/**
 * translates all expressions based on the translation xml file<br>
 * translation expressions must be in the format &lt;span class="lang_???"&gt;&lt;/span&gt;, where ??? is
 * the number of the translated expression in the xml file<br><br>if a translated expression is not found in the xml
 * file nothing would be translated, so the initial value which is inside the span tag is displayed
 * @param {String} [plugin] name of the plugin
 */
function changeLanguage(plugin) {
    var langarrId = current_language + "_";

    if (plugin === undefined) {
        langarrId += "phpSysInfo";
    } else {
        langarrId += plugin;
    }

    if (langxml[langarrId] !== undefined) {
        changeSpanLanguage(plugin);
    } else {
        langxml.push(langarrId);
        getLanguage(plugin, langarrId);
    }
}

function changeSpanLanguage(plugin) {
    var langId = "", langStr = "", langarrId = current_language + "_";

    if (plugin === undefined) {
        langarrId += "phpSysInfo";
        $('span[class*=lang_]').each(function translate(i) {
            langId = this.className.substring(5);
            if (langId.indexOf('plugin_') !== 0) { //does not begin with plugin_
                langStr = langarr[langarrId][langId];
                if (langStr !== undefined) {
                    if (langStr.length > 0) {
                        this.innerHTML = langStr;
                    }
                }
            }
        });
        $("#loader").hide();
        $("#output").fadeIn("slow"); //show if any language loaded
    } else {
        langarrId += plugin;
        $('span[class*=lang_plugin_'+plugin.toLowerCase()+'_]').each(function translate(i) {
            langId = this.className.substring(5);
            langStr = langarr[langarrId][langId];
            if (langStr !== undefined) {
                if (langStr.length > 0) {
                    this.innerHTML = langStr;
                }
            }
        });
        $('#panel_'+plugin).show(); //show plugin if any language loaded
    }
}

/**
 * generate the filesystemTable and activate the dataTables plugin on it
 */
function filesystemtable() {
    var html = "";
    html += "<h2>" + genlang(30) + "</h2>\n";
    html += "        <div style=\"overflow-x:auto;\">\n";
    html += "          <table id=\"filesystemTable\" style=\"border-collapse:collapse;\">\n";
    html += "            <thead>\n";
    html += "              <tr>\n";
    html += "                <th>" + genlang(31) + "</th>\n";
    html += "                <th>" + genlang(34) + "</th>\n";
    html += "                <th>" + genlang(32) + "</th>\n";
    html += "                <th>" + genlang(33) + "</th>\n";
    html += "                <th class=\"right\">" + genlang(35) + "</th>\n";
    html += "                <th class=\"right\">" + genlang(36) + "</th>\n";
    html += "                <th class=\"right\">" + genlang(37) + "</th>\n";
    html += "              </tr>\n";
    html += "            </thead>\n";
    html += "            <tfoot>\n";
    html += "              <tr style=\"font-weight : bold\">\n";
    html += "                <td>&nbsp;</td>\n";
    html += "                <td>&nbsp;</td>\n";
    html += "                <td>" + genlang(38) + "</td>\n";
    html += "                <td id=\"s_fs_total\"></td>\n";
    html += "                <td class=\"right\"><span id=\"s_fs_tfree\"></span></td>\n";
    html += "                <td class=\"right\"><span id=\"s_fs_tused\"></span></td>\n";
    html += "                <td class=\"right\"><span id=\"s_fs_tsize\"></span></td>\n";
    html += "              </tr>\n";
    html += "            </tfoot>\n";
    html += "            <tbody>\n";
    html += "            </tbody>\n";
    html += "          </table>\n";
    html += "        </div>\n";

    $("#filesystem").append(html);

    filesystemTable = $("#filesystemTable").dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": true,
        "bInfo": false,
        "bProcessing": true,
        "bAutoWidth": false,
        "bStateSave": true,
        "aoColumns": [{
            "sType": 'span-string',
            "sWidth": "100px"
        }, {
            "sType": 'span-string',
            "sWidth": "50px"
        }, {
            "sType": 'span-string',
            "sWidth": "200px"
        }, {
            "sType": 'span-number'
        }, {
            "sType": 'span-number',
            "sWidth": "80px",
            "sClass": "right"
        }, {
            "sType": 'span-number',
            "sWidth": "80px",
            "sClass": "right"
        }, {
            "sType": 'span-number',
            "sWidth": "80px",
            "sClass": "right"
        }]
    });
}

/**
 * fill all errors from the xml in the error div element in the document and show the error icon
 * @param {jQuery} xml phpSysInfo-XML
 */
function populateErrors(xml) {
    var values = false;
    $("Errors Error", xml).each(function getError(id) {
//        $("#errorlist").append("<b>" + $(this).attr("Function") + "</b><br><br><pre>" + $(this).text() + "</pre><hr>");
        $("#errorlist").append("<b>" + $(this).attr("Function") + "</b><br><br><pre>" + $(this).attr("Message") + "</pre><hr>");
        values = true;
    });
    if (values) {
        $("#warn").css("display", "inline");
        $("#loadwarn").css("display", "inline");
    }
}

/**
 * show the page
 * @param {jQuery} xml phpSysInfo-XML
 */
function displayPage(xml) {
    var versioni = "";
//    $("#loader").hide();
//    $("#output").fadeIn("slow");
    versioni = $("Generation", xml).attr("version").toString();
    $("#version").html(versioni);
}

/**
 * format seconds to a better readable statement with days, hours and minutes
 * @param {Number} sec seconds that should be formatted
 * @return {String} html string with no breaking spaces and translation statements
 */
function formatUptime(sec) {
    var txt = "", intMin = 0, intHours = 0, intDays = 0;
    intMin = sec / 60;
    intHours = intMin / 60;
    intDays = Math.floor(intHours / 24);
    intHours = Math.floor(intHours - (intDays * 24));
    intMin = Math.floor(intMin - (intDays * 60 * 24) - (intHours * 60));
    if (intDays) {
        txt += intDays.toString() + "&nbsp;" + genlang(48) + "&nbsp;";
    }
    if (intHours) {
        txt += intHours.toString() + "&nbsp;" + genlang(49) + "&nbsp;";
    }
    return txt + intMin.toString() + "&nbsp;" + genlang(50);
}

/**
 * format a given MHz value to a better readable statement with the right suffix
 * @param {Number} mhertz mhertz value that should be formatted
 * @return {String} html string with no breaking spaces and translation statements
 */
function formatHertz(mhertz) {
    if ((mhertz >= 0) && (mhertz < 1000)) {
        return mhertz.toString() + "&nbsp;" + genlang(92);
    } else {
        if (mhertz >= 1000) {
            return round(mhertz / 1000, 2) + "&nbsp;" + genlang(93);
        } else {
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
function formatBytes(bytes, xml) {
    var byteFormat = "", show = "";

    $("Options", xml).each(function getByteFormat(id) {
        byteFormat = $(this).attr("byteFormat");
    });

    switch (byteFormat.toLowerCase()) {
    case "pib":
        show += round(bytes / Math.pow(1024, 5), 2);
        show += "&nbsp;" + genlang(90);
        break;
    case "tib":
        show += round(bytes / Math.pow(1024, 4), 2);
        show += "&nbsp;" + genlang(86);
        break;
    case "gib":
        show += round(bytes / Math.pow(1024, 3), 2);
        show += "&nbsp;" + genlang(87);
        break;
    case "mib":
        show += round(bytes / Math.pow(1024, 2), 2);
        show += "&nbsp;" + genlang(88);
        break;
    case "kib":
        show += round(bytes / Math.pow(1024, 1), 2);
        show += "&nbsp;" + genlang(89);
        break;
    case "pb":
        show += round(bytes / Math.pow(1000, 5), 2);
        show += "&nbsp;" + genlang(91);
        break;
    case "tb":
        show += round(bytes / Math.pow(1000, 4), 2);
        show += "&nbsp;" + genlang(85);
        break;
    case "gb":
        show += round(bytes / Math.pow(1000, 3), 2);
        show += "&nbsp;" + genlang(41);
        break;
    case "mb":
        show += round(bytes / Math.pow(1000, 2), 2);
        show += "&nbsp;" + genlang(40);
        break;
    case "kb":
        show += round(bytes / Math.pow(1000, 1), 2);
        show += "&nbsp;" + genlang(39);
        break;
    case "b":
        show += bytes;
        show += "&nbsp;" + genlang(96);
        break;
    case "auto_decimal":
        if (bytes > Math.pow(1000, 5)) {
            show += round(bytes / Math.pow(1000, 5), 2);
            show += "&nbsp;" + genlang(91);
        } else {
            if (bytes > Math.pow(1000, 4)) {
                show += round(bytes / Math.pow(1000, 4), 2);
                show += "&nbsp;" + genlang(85);
            } else {
                if (bytes > Math.pow(1000, 3)) {
                    show += round(bytes / Math.pow(1000, 3), 2);
                    show += "&nbsp;" + genlang(41);
                } else {
                    if (bytes > Math.pow(1000, 2)) {
                        show += round(bytes / Math.pow(1000, 2), 2);
                        show += "&nbsp;" + genlang(40);
                    } else {
                        if (bytes > Math.pow(1000, 1)) {
                            show += round(bytes / Math.pow(1000, 1), 2);
                            show += "&nbsp;" + genlang(39);
                        } else {
                                show += bytes;
                                show += "&nbsp;" + genlang(96);
                        }
                    }
                }
            }
        }
        break;
    default:
        if (bytes > Math.pow(1024, 5)) {
            show += round(bytes / Math.pow(1024, 5), 2);
            show += "&nbsp;" + genlang(90);
        } else {
            if (bytes > Math.pow(1024, 4)) {
                show += round(bytes / Math.pow(1024, 4), 2);
                show += "&nbsp;" + genlang(86);
            } else {
                if (bytes > Math.pow(1024, 3)) {
                    show += round(bytes / Math.pow(1024, 3), 2);
                    show += "&nbsp;" + genlang(87);
                } else {
                    if (bytes > Math.pow(1024, 2)) {
                        show += round(bytes / Math.pow(1024, 2), 2);
                        show += "&nbsp;" + genlang(88);
                    } else {
                        if (bytes > Math.pow(1024, 1)) {
                            show += round(bytes / Math.pow(1024, 1), 2);
                            show += "&nbsp;" + genlang(89);
                        } else {
                            show += bytes;
                            show += "&nbsp;" + genlang(96);
                        }
                    }
                }
            }
        }
    }
    return show;
}

function formatBPS(bps) {
    var show = "";

    if (bps > Math.pow(1000, 5)) {
        show += round(bps / Math.pow(1000, 5), 2);
        show += String.fromCharCode(160) + 'Pb/s';
    } else {
        if (bps > Math.pow(1000, 4)) {
            show += round(bps / Math.pow(1000, 4), 2);
            show += String.fromCharCode(160) + 'Tb/s';
        } else {
            if (bps > Math.pow(1000, 3)) {
                show += round(bps / Math.pow(1000, 3), 2);
                show += String.fromCharCode(160) + 'Gb/s';
            } else {
                if (bps > Math.pow(1000, 2)) {
                    show += round(bps / Math.pow(1000, 2), 2);
                    show += String.fromCharCode(160) + 'Mb/s';
                } else {
                    if (bps > Math.pow(1000, 1)) {
                        show += round(bps / Math.pow(1000, 1), 2);
                        show += String.fromCharCode(160) + 'Kb/s';
                    } else {
                            show += bps;
                            show += String.fromCharCode(160) + 'b/s';
                    }
                }
            }
        }
    }
    return show;
}

/**
 * format a celcius temperature to fahrenheit and also append the right suffix
 * @param {String} degreeC temperature in celvius
 * @param {jQuery} xml phpSysInfo-XML
 * @return {String} html string with no breaking spaces and translation statements
 */
function formatTemp(degreeC, xml) {
    var tempFormat = "", degree = 0;

    $("Options", xml).each(function getOptions(id) {
        tempFormat = $(this).attr("tempFormat").toString().toLowerCase();
    });

    degree = parseFloat(degreeC);
    if (isNaN(degreeC)) {
        return "---";
    } else {
        switch (tempFormat) {
        case "f":
            return round((((9 * degree) / 5) + 32), 1) + "&nbsp;" + genlang(61);
        case "c":
            return round(degree, 1) + "&nbsp;" + genlang(60);
        case "c-f":
            return round(degree, 1) + "&nbsp;" + genlang(60) + "<br><i>(" + round((((9 * degree) / 5) + 32), 1) + "&nbsp;" + genlang(61) + ")</i>";
        case "f-c":
            return round((((9 * degree) / 5) + 32), 1) + "&nbsp;" + genlang(61) + "<br><i>(" + round(degree, 1) + "&nbsp;" + genlang(60) + ")</i>";
        }
    }
}

/**
 * create a visual HTML bar from a given size, the layout of that bar can be costumized through the bar css-class
 * @param {Number} size barclass
 * @return {String} HTML string which contains the full layout of the bar
 */
function createBar(size, barclass) {
    if (barclass === undefined) {
        barclass = "bar";
    }
    return "<div class=\"" + barclass + "\" style=\"float:left; width: " + Math.max(Math.min(Math.round(size), 100), 0) + "px;\">&nbsp;</div>&nbsp;" + size + "%";
}

/**
 * (re)fill the vitals block with the values from the given xml
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshVitals(xml) {
    var hostname = "", ip = "";
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('vitals', blocks) < 0))) {
        $("#vitals").remove();
        $("Vitals", xml).each(function getVitals(id) {
            hostname = $(this).attr("Hostname");
            ip = $(this).attr("IPAddr");
            document.title = "System information: " + hostname + " (" + ip + ")";
            $("#s_hostname_title").html(hostname);
            $("#s_ip_title").html(ip);
        });
        return;
    }

    var kernel = "", distro = "", icon = "", uptime = "", users = 0, loadavg = "", os = "";
    var processes = 0, prunning = 0, psleeping = 0, pstopped = 0, pzombie = 0, pwaiting = 0, pother = 0;
    var syslang = "", codepage = "";
    var lastboot = 0;
    var timestamp = parseInt($("Generation", xml).attr("timestamp"), 10)*1000; //server time
    var not_first = false;
    var datetimeFormat = "";
    if (isNaN(timestamp)) timestamp = Number(new Date()); //client time

    $("Options", xml).each(function getDatetimeFormat(id) {
        datetimeFormat = $(this).attr("datetimeFormat");
    });

    $("Vitals", xml).each(function getVitals(id) {
        hostname = $(this).attr("Hostname");
        ip = $(this).attr("IPAddr");
        kernel = $(this).attr("Kernel");
        distro = $(this).attr("Distro");
        icon = $(this).attr("Distroicon");
        os = $(this).attr("OS");
        uptime = formatUptime(parseInt($(this).attr("Uptime"), 10));
        lastboot = new Date(timestamp - (parseInt($(this).attr("Uptime"), 10)*1000));
        users = parseInt($(this).attr("Users"), 10);
        loadavg = $(this).attr("LoadAvg");
        if ($(this).attr("CPULoad") !== undefined) {
            loadavg = loadavg + "<br>" + createBar(parseInt($(this).attr("CPULoad"), 10));
        }
        if ($(this).attr("SysLang") !== undefined) {
            syslang = $(this).attr("SysLang");
            document.getElementById("s_syslang_tr").style.display='';
        }

        if ($(this).attr("CodePage") !== undefined) {
            codepage = $(this).attr("CodePage");
            if ($(this).attr("SysLang") !== undefined) {
                document.getElementById("s_codepage_tr1").style.display='';
            } else {
                document.getElementById("s_codepage_tr2").style.display='';
            }
        }

        //processes
        if ($(this).attr("Processes") !== undefined) {
            processes = parseInt($(this).attr("Processes"), 10);
            if ((($(this).attr("CodePage") !== undefined) && ($(this).attr("SysLang") === undefined)) ||
                (($(this).attr("CodePage") === undefined) && ($(this).attr("SysLang") !== undefined))) {
                document.getElementById("s_processes_tr1").style.display='';
            } else {
                document.getElementById("s_processes_tr2").style.display='';
            }
        }
        if ($(this).attr("ProcessesRunning") !== undefined) {
            prunning = parseInt($(this).attr("ProcessesRunning"), 10);
        }
        if ($(this).attr("ProcessesSleeping") !== undefined) {
            psleeping = parseInt($(this).attr("ProcessesSleeping"), 10);
        }
        if ($(this).attr("ProcessesStopped") !== undefined) {
            pstopped = parseInt($(this).attr("ProcessesStopped"), 10);
        }
        if ($(this).attr("ProcessesZombie") !== undefined) {
            pzombie = parseInt($(this).attr("ProcessesZombie"), 10);
        }
        if ($(this).attr("ProcessesWaiting") !== undefined) {
            pwaiting = parseInt($(this).attr("ProcessesWaiting"), 10);
        }
        if ($(this).attr("ProcessesOther") !== undefined) {
            pother = parseInt($(this).attr("ProcessesOther"), 10);
        }

        document.title = "System information: " + hostname + " (" + ip + ")";
        $("#s_hostname_title").html(hostname);
        $("#s_ip_title").html(ip);
        $("#s_hostname").html(hostname);
        $("#s_ip").html(ip);
        $("#s_kernel").html(kernel);
        $("#s_distro").html("<img src='./gfx/images/" + icon + "' alt='Icon' title='' style='width:16px;height:16px;vertical-align:middle;' onload='PNGload($(this));' />&nbsp;" + distro); //onload IE6 PNG fix
        $("#s_os").html("<img src='./gfx/images/" + os + ".png' alt='OSIcon' title='' style='width:16px;height:16px;vertical-align:middle;' onload='PNGload($(this));' />&nbsp;" + os); //onload IE6 PNG fix
        $("#s_uptime").html(uptime);
        if ((datetimeFormat !== undefined) && (datetimeFormat.toLowerCase() === "locale")) {
            $("#s_lastboot").html(lastboot.toLocaleString());
        } else {
            if (typeof(lastboot.toUTCString)==="function") {
                $("#s_lastboot").html(lastboot.toUTCString());
            } else {
                //deprecated
                $("#s_lastboot").html(lastboot.toGMTString());
            }
        }
        $("#s_users").html(users);
        $("#s_loadavg").html(loadavg);
        $("#s_syslang").html(syslang);
        $("#s_codepage_1").html(codepage);
        $("#s_codepage_2").html(codepage);
        $("#s_processes_1").html(processes);
        $("#s_processes_2").html(processes);
        if (prunning || psleeping || pstopped || pzombie || pwaiting || pother) {
            $("#s_processes_1").append(" (");
            $("#s_processes_2").append(" (");
            var typelist = {running:111,sleeping:112,stopped:113,zombie:114,waiting:115,other:116};
            for (var proc_type in typelist) {
                if (eval("p" + proc_type)) {
                    if (not_first) {
                        $("#s_processes_1").append(", ");
                        $("#s_processes_2").append(", ");
                    }
                    $("#s_processes_1").append(eval("p" + proc_type) + "&nbsp;" + genlang(typelist[proc_type]));
                    $("#s_processes_2").append(eval("p" + proc_type) + "&nbsp;" + genlang(typelist[proc_type]));
                    not_first = true;
                }
            }
            $("#s_processes_1").append(") ");
            $("#s_processes_2").append(") ");
        }
    });
}


/**
 * build the cpu information as table rows
 * @param {jQuery} xml phpSysInfo-XML
 * @param {Array} tree array that holds the positions for treetable plugin
 * @param {Number} rootposition position of the parent element
 * @param {Array} collapsed array that holds all collapsed elements hwne opening page
 */
function fillCpu(xml, tree, rootposition, collapsed) {
    var cpucount = 0, html = "";
    $("Hardware CPU CpuCore", xml).each(function getCpuCore(cpuCoreId) {
        var model = "", speed = 0, bus = 0, cache = 0, bogo = 0, temp = 0, load = 0, speedmax = 0, speedmin = 0, cpucoreposition = 0, virt = "", manufacturer = "";
        cpucount++;
        model = $(this).attr("Model");
        speed = parseInt($(this).attr("CpuSpeed"), 10);
        speedmax = parseInt($(this).attr("CpuSpeedMax"), 10);
        speedmin = parseInt($(this).attr("CpuSpeedMin"), 10);
        cache = parseInt($(this).attr("Cache"), 10);
        virt = $(this).attr("Virt");
        bus = parseInt($(this).attr("BusSpeed"), 10);
        temp = parseInt($(this).attr("Cputemp"), 10);
        bogo = parseInt($(this).attr("Bogomips"), 10);
        manufacturer = $(this).attr("Manufacturer");
        load = parseInt($(this).attr("Load"), 10);

        if (!showCPUListExpanded) {
            collapsed.push(rootposition);
        }
        if (!isNaN(load) && showCPULoadCompact) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + model + "</span></div></td><td>" + createBar(load) + "</td></tr>\n";
        } else {
            html += "<tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespan\">" + model + "</span></div></td></tr>\n";
        }
        cpucoreposition = tree.push(rootposition);
        if (!showCPUInfoExpanded) {
            collapsed.push(cpucoreposition);
        }
        if (!isNaN(speed)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(13) + ":</span></div></td><td>" + formatHertz(speed) + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
        if (!isNaN(speedmax)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(100) + ":</span></div></td><td>" + formatHertz(speedmax) + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
        if (!isNaN(speedmin)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(101) + ":</span></div></td><td>" + formatHertz(speedmin) + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
        if (!isNaN(cache)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(15) + ":</span></div></td><td>" + formatBytes(cache, xml) + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
        if (virt !== undefined) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(94) + ":</span></div></td><td>" + virt + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
        if (!isNaN(bus)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(14) + ":</span></div></td><td>" + formatHertz(bus) + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
        if (!isNaN(bogo)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(16) + ":</span></div></td><td>" + bogo.toString() + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
/*
        if (!isNaN(temp)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(51) + ":</span></div></td><td>" + formatTemp(temp, xml) + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
*/
        if (manufacturer !== undefined) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(122) + ":</span></div></td><td>" + manufacturer + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
        if (!isNaN(load) && !showCPULoadCompact) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(9) + ":</span></div></td><td>" + createBar(load) + "</td></tr>\n";
            tree.push(cpucoreposition);
        }
    });
    if (cpucount === 0) {
        html += "<tr><td colspan=\"2\">" + genlang(42) + "</td></tr>\n";
        tree.push(rootposition);
    }
    return html;
}

function countCpu(xml) {
    var cpucount = 0;
    $("Hardware CPU CpuCore", xml).each(function getCpuCore(cpuCoreId) {
        cpucount++;
    });
    return cpucount;
}

/**
 * build rows for a treetable out of the hardwaredevices
 * @param {jQuery} xml phpSysInfo-XML
 * @param {String} type type of the hardware device
 * @param {Array} tree array that holds the positions for treetable plugin
 * @param {Number} rootposition position of the parent element
 */
function fillHWDevice(xml, type, tree, rootposition) {
    var devicecount = 0, html = "";
    $("Hardware " + type + " Device", xml).each(function getHWDevice(deviceId) {
        var name = "", count = 0, capacity = 0, manufacturer = "", product = "", serial = "", devcoreposition = 0;

        devicecount++;
        name = $(this).attr("Name");
        capacity = parseInt($(this).attr("Capacity"), 10);
        manufacturer = $(this).attr("Manufacturer");
        product = $(this).attr("Product");
        serial = $(this).attr("Serial");
        count = parseInt($(this).attr("Count"), 10);
        if (!isNaN(count) && count > 1) {
            name = "(" + count + "x) " + name;
        }
        html += "<tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespan\">" + name + "</span></div></td></tr>\n";
        devcoreposition = tree.push(rootposition);
        if (!isNaN(capacity)) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(43) + ":</span></div></td><td>" + formatBytes(capacity, xml) + "</td></tr>\n";
            tree.push(devcoreposition);
        }
        if (manufacturer!== undefined) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(122) + ":</span></div></td><td>" + manufacturer + "</td></tr>\n";
            tree.push(devcoreposition);
        }
        if (product !== undefined) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(123) + ":</span></div></td><td>" + product + "</td></tr>\n";
            tree.push(devcoreposition);
        }
        if (serial !== undefined) {
            html += "<tr><td style=\"width:68%\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(124) + ":</span></div></td><td>" + serial + "</td></tr>\n";
            tree.push(devcoreposition);
        }
    });
    if (devicecount === 0) {
        html += "<tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(42) + "</span></div></td></tr>\n";
        tree.push(rootposition);
    }
    return html;
}

function countHWDevice(xml, type) {
    var devicecount = 0;
    $("Hardware " + type + " Device", xml).each(function getHWDevice(deviceId) {
        devicecount++;
    });
    return devicecount;
}

/**
 * (re)fill the hardware block with the values from the given xml
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshHardware(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('hardware', blocks) < 0))) {
        $("#hardware").remove();
        return;
    }

    var html = "", tree = [], closed = [], index = 0, machine = "";
    $("#hardware").empty();
    html += "<h2>" + genlang(10) + "</h2>\n";
    html += " <div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"HardwareTree\" class=\"tablemain\">\n";
    html += "   <tbody class=\"tree\">\n";

    $("Hardware", xml).each(function getMachine(id) {
        machine = $(this).attr("Name");
    });
    if ((machine !== undefined) && (machine !== "")) {
        html += "    <tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespanbold\">" + genlang(107) + "</span></div></td></tr>\n";
        html += "<tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespan\">" + machine + "</span></div></td></tr>\n";
        tree.push(tree.push(0));
    }

    if (countCpu(xml)) {
        html += "    <tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespanbold\">" + genlang(11) + "</span></div></td></tr>\n";
        html += fillCpu(xml, tree, tree.push(0), closed);
    }

    var typelist = {PCI:17,IDE:18,SCSI:19,NVMe:126,USB:20,TB:117,I2C:118};
    for (var dev_type in typelist) {
        if (countHWDevice(xml, dev_type)) {
            html += "    <tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespanbold\">" + genlang(typelist[dev_type]) + "</span></div></td></tr>\n";
            index = tree.push(0);
            closed.push(index);
            html += fillHWDevice(xml, dev_type, tree, index);
        }
    }

    html += "   </tbody>\n";
    html += "  </table>\n";
    html += " </div>\n";
    $("#hardware").append(html);

    $("#HardwareTree").jqTreeTable(tree, {
        openImg: "./gfx/treeTable/tv-collapsable.gif",
        shutImg: "./gfx/treeTable/tv-expandable.gif",
        leafImg: "./gfx/treeTable/tv-item.gif",
        lastOpenImg: "./gfx/treeTable/tv-collapsable-last.gif",
        lastShutImg: "./gfx/treeTable/tv-expandable-last.gif",
        lastLeafImg: "./gfx/treeTable/tv-item-last.gif",
        vertLineImg: "./gfx/treeTable/vertline.gif",
        blankImg: "./gfx/treeTable/blank.gif",
        collapse: closed,
        column: 0,
        striped: true,
        highlight: false,
        state: false
    });
}

/**
 *(re)fill the network block with the values from the given xml
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshNetwork(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('network', blocks) < 0))) {
        $("#network").remove();
        return;
    }

    var tree = [], closed = [], html0= "", html1= "" ,html = "", isinfo = false, preoldnetwork = [], timestamp;

    $("#network").empty();

    html0 += "<h2>" + genlang(21) + "</h2>\n";

    html1 += "   <thead>\n";
    html1 += "    <tr>\n";
    html1 += "     <th>" + genlang(22) + "</th>\n";
    html1 += "     <th class=\"right\" style=\"width:50px;\">" + genlang(23) + "</th>\n";
    html1 += "     <th class=\"right\" style=\"width:50px;\">" + genlang(24) + "</th>\n";
    html1 += "     <th class=\"right\" style=\"width:50px;\">" + genlang(25) + "</th>\n";
    html1 += "    </tr>\n";
    html1 += "   </thead>\n";

    if (showNetworkActiveSpeed) {
        $("Generation", xml).each(function getTimestamp(id) {
            timestamp = $(this).attr("timestamp");
        });
    }

    $("Network NetDevice", xml).each(function getDevice(id) {
        var name = "", rx = 0, tx = 0, er = 0, dr = 0, info = "", networkindex = 0, htmlrx = '', htmltx = '';
        name = $(this).attr("Name");
        rx = parseInt($(this).attr("RxBytes"), 10);
        tx = parseInt($(this).attr("TxBytes"), 10);
        er = parseInt($(this).attr("Err"), 10);
        dr = parseInt($(this).attr("Drops"), 10);

        if (showNetworkActiveSpeed && ($.inArray(name, oldnetwork) >= 0)) {
            var diff, difftime;
            if (((diff = rx - oldnetwork[name].rx) > 0) && ((difftime = timestamp - oldnetwork[name].timestamp) > 0)) {
                if (showNetworkActiveSpeed == 2) {
                    htmlrx ="<br><i>("+formatBPS(round(8*diff/difftime, 2))+")</i>";
                } else {
                    htmlrx ="<br><i>("+formatBytes(round(diff/difftime, 2), xml)+"/s)</i>";
                }
            }
            if (((diff = tx - oldnetwork[name].tx) > 0) && (difftime > 0)) {
                if (showNetworkActiveSpeed == 2) {
                    htmltx ="<br><i>("+formatBPS(round(8*diff/difftime, 2))+")</i>";
                } else {
                    htmltx ="<br><i>("+formatBytes(round(diff/difftime, 2), xml)+"/s)</i>";
                }
            }
        }

        html +="<tr><td><div class=\"treediv\"><span class=\"treespan\">" + name + "</span></div></td><td class=\"right\">" + formatBytes(rx, xml) + htmlrx + "</td><td class=\"right\">" + formatBytes(tx, xml) + htmltx +"</td><td class=\"right\">" + er.toString() + "/<wbr>" + dr.toString() + "</td></tr>";

        networkindex = tree.push(0);

        if (showNetworkActiveSpeed) {
            preoldnetwork.pushIfNotExist(name);
            preoldnetwork[name] = {timestamp:timestamp, rx:rx, tx:tx};
        }

        info = $(this).attr("Info");
        if ( (info !== undefined) && (info !== "") ) {
            var i = 0, infos = info.replace(/:/g, "<wbr>:").split(";"); /* split long addresses */
            isinfo = true;
            for(i = 0; i < infos.length; i++){
                html +="<tr><td colspan=\"4\"><div class=\"treediv\"><span class=\"treespan\">" + infos[i] + "</span></div></td></tr>";
                tree.push(networkindex);
            }
            if (!showNetworkInfosExpanded) {
                closed.push(networkindex);
            }
        }
    });
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";
    html0 += "<div style=\"overflow-x:auto;\">\n";
    if (isinfo) {
       html0 += "  <table id=\"NetworkTree\" class=\"tablemain\">\n";
       html1 += "   <tbody class=\"tree\">\n";
    } else {
       html0 += "  <table id=\"NetworkTree\" class=\"stripeMe\" style=\"border-collapse:collapse;\">\n";
       html1 += "   <tbody class=\"tbody_network\">\n";
    }
    $("#network").append(html0+html1+html);

    if (isinfo) $("#NetworkTree").jqTreeTable(tree, {
        openImg: "./gfx/treeTable/tv-collapsable.gif",
        shutImg: "./gfx/treeTable/tv-expandable.gif",
        leafImg: "./gfx/treeTable/tv-item.gif",
        lastOpenImg: "./gfx/treeTable/tv-collapsable-last.gif",
        lastShutImg: "./gfx/treeTable/tv-expandable-last.gif",
        lastLeafImg: "./gfx/treeTable/tv-item-last.gif",
        vertLineImg: "./gfx/treeTable/vertline.gif",
        blankImg: "./gfx/treeTable/blank.gif",
        collapse: closed,
        column: 0,
        striped: true,
        highlight: false,
        state: false
      });

    if (showNetworkActiveSpeed) {
        while (oldnetwork.length > 0) {
            delete oldnetwork[oldnetwork.length-1]; //remove last object
            oldnetwork.pop(); //remove last object reference from array
        }
        oldnetwork = preoldnetwork;
    }
}

/**
 * (re)fill the memory block with the values from the given xml
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshMemory(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('memory', blocks) < 0))) {
        $("#memory").remove();
        return;
    }

    var html = "", tree = [], closed = [];

    $("#memory").empty();
    html += "<h2>" + genlang(27) + "</h2>\n";
    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"MemoryTree\" class=\"tablemain\">\n";
    html += "   <thead>\n";
    html += "     <tr>\n";
    html += "      <th style=\"width:200px;\">" + genlang(34) + "</th>\n";
    html += "      <th style=\"width:285px;\">" + genlang(33) + "</th>\n";
    html += "      <th class=\"right\" style=\"width:100px;\">" + genlang(125) + "</th>\n";
    html += "      <th class=\"right\" style=\"width:100px;\">" + genlang(36) + "</th>\n";
    html += "      <th class=\"right\" style=\"width:100px;\">" + genlang(37) + "</th>\n";
    html += "     </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody class=\"tree\">\n";

    $("Memory", xml).each(function getMemory(id) {
        var free = 0, total = 0, used = 0, percent = 0, memoryindex = 0;
        free = parseInt($(this).attr("Free"), 10);
        used = parseInt($(this).attr("Used"), 10);
        total = parseInt($(this).attr("Total"), 10);
        percent = parseInt($(this).attr("Percent"), 10);
        html += "<tr><td style=\"width:200px;\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(28) + "</span></div></td><td style=\"width:285px;\">" + createBar(percent) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(free, xml) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(used, xml) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(total, xml) + "</td></tr>";
        memoryindex = tree.push(0);

        $("Memory Details", xml).each(function getMemorydetails(id) {
            var app = 0, appp = 0, buff = 0, buffp = 0, cached = 0, cachedp = 0;
            app = parseInt($(this).attr("App"), 10);
            appp = parseInt($(this).attr("AppPercent"), 10);
            buff = parseInt($(this).attr("Buffers"), 10);
            buffp = parseInt($(this).attr("BuffersPercent"), 10);
            cached = parseInt($(this).attr("Cached"), 10);
            cachedp = parseInt($(this).attr("CachedPercent"), 10);
            if (!isNaN(app)) {
                html += "<tr><td style=\"width:184px;\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(64) + "</span></div></td><td style=\"width:285px;\">" + createBar(appp) + "</td><td class=\"right\" style=\"width:100px;\">&nbsp;</td><td class=\"right\" style=\"width:100px\">" + formatBytes(app, xml) + "</td><td class=\"right\" style=\"width:100px;\">&nbsp;</td></tr>";
                tree.push(memoryindex);
            }
            if (!isNaN(cached)) {
                html += "<tr><td style=\"width:184px;\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(66) + "</span></div></td><td style=\"width:285px;\">" + createBar(cachedp) + "</td><td class=\"right\" style=\"width:100px;\">&nbsp;</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(cached, xml) + "</td><td class=\"right\" style=\"width:100px;\">&nbsp;</td></tr>";
                tree.push(memoryindex);
            }
            if (!isNaN(buff)) {
                html += "<tr><td style=\"width:184px;\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(65) + "</span></div></td><td style=\"width:285px\">" + createBar(buffp) + "</td><td class=\"rigth\" style=\"width:100px;\">&nbsp;</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(buff, xml) + "</td><td class=\"right\" style=\"width:100px;\">&nbsp;</td></tr>";
                tree.push(memoryindex);
            }
            if (!isNaN(app) || !isNaN(buff) || !isNaN(cached)) {
                if (!showMemoryInfosExpanded) {
                    closed.push(memoryindex);
                }
            }
        });
    });
    $("Memory Swap", xml).each(function getSwap(id) {
        var free = 0, total = 0, used = 0, percent = 0, swapindex = 0;
        free = parseInt($(this).attr("Free"), 10);
        used = parseInt($(this).attr("Used"), 10);
        total = parseInt($(this).attr("Total"), 10);
        percent = parseInt($(this).attr("Percent"), 10);
        html += "<tr><td style=\"width:200px;\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(29) + "</span></div></td><td style=\"width:285px;\">" + createBar(percent) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(free, xml) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(used, xml) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(total, xml) + "</td></tr>";
        swapindex = tree.push(0);

        $("Memory Swap Mount", xml).each(function getDevices(id) {
            var free = 0, total = 0, used = 0, percent = 0, mpoint = "", mpid = 0;
            if (!showMemoryInfosExpanded) {
                    closed.push(swapindex);
            }
            free = parseInt($(this).attr("Free"), 10);
            used = parseInt($(this).attr("Used"), 10);
            total = parseInt($(this).attr("Total"), 10);
            percent = parseInt($(this).attr("Percent"), 10);
            mpid = parseInt($(this).attr("MountPointID"), 10);
            mpoint = $(this).attr("MountPoint");

            if (mpoint === undefined) {
                mpoint = mpid;
            }

            html += "<tr><td style=\"width:184px;\"><div class=\"treediv\"><span class=\"treespan\">" + mpoint + "</span></div></td><td style=\"width:285px;\">" + createBar(percent) + "</td><td class=\"right\" style=\"width:100px\">" + formatBytes(free, xml) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(used, xml) + "</td><td class=\"right\" style=\"width:100px;\">" + formatBytes(total, xml) + "</td></tr>";
            tree.push(swapindex);
        });
    });

    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";
    $("#memory").append(html);

    $("#MemoryTree").jqTreeTable(tree, {
        openImg: "./gfx/treeTable/tv-collapsable.gif",
        shutImg: "./gfx/treeTable/tv-expandable.gif",
        leafImg: "./gfx/treeTable/tv-item.gif",
        lastOpenImg: "./gfx/treeTable/tv-collapsable-last.gif",
        lastShutImg: "./gfx/treeTable/tv-expandable-last.gif",
        lastLeafImg: "./gfx/treeTable/tv-item-last.gif",
        vertLineImg: "./gfx/treeTable/vertline.gif",
        blankImg: "./gfx/treeTable/blank.gif",
        collapse: closed,
        column: 0,
        striped: true,
        highlight: false,
        state: false
    });

}

/**
 * (re)fill the filesystems block with the values from the given xml<br><br>
 * appends the filesystems (each in a row) to the filesystem table in the tbody<br>before the rows are inserted the entire
 * tbody is cleared
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshFilesystems(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('filesystem', blocks) < 0))) {
        $("#filesystem").remove();
        return;
    }

    var total_usage = 0, total_used = 0, total_free = 0, total_size = 0, threshold = 0;

    filesystemTable.fnClearTable();

    $("Options", xml).each(function getThreshold(id) {
        threshold = parseInt($(this).attr("threshold"), 10);
    });

    $("FileSystem Mount", xml).each(function getMount(mid) {
        var mpoint = "", mpid = 0, type = "", name = "", free = 0, used = 0, size = 0, percent = 0, options = "", inodes = 0, inodes_text = "", options_text = "", ignore = 0;
        mpid = parseInt($(this).attr("MountPointID"), 10);
        type = $(this).attr("FSType");
        name = $(this).attr("Name").replace(/;/g, ";<wbr>"); /* split long name */
        free = parseInt($(this).attr("Free"), 10);
        used = parseInt($(this).attr("Used"), 10);
        size = parseInt($(this).attr("Total"), 10);
        percent = parseInt($(this).attr("Percent"), 10);
        options = $(this).attr("MountOptions");
        inodes = parseInt($(this).attr("Inodes"), 10);
        mpoint = $(this).attr("MountPoint");
        ignore = parseInt($(this).attr("Ignore"), 10);

        if (mpoint === undefined) {
            mpoint = mpid;
        }
        if (options !== undefined) {
            options_text = "<br><i>(" + options + ")</i>";
        }
        if (!isNaN(inodes)) {
            inodes_text = "<span style=\"font-style:italic\">&nbsp;(" + inodes.toString() + "%)</span>";
        }

        if (!isNaN(ignore) && (ignore > 0)) {
            if (ignore >= 2) {
                if ((ignore == 2) && !isNaN(threshold) && (percent >= threshold)) {
                    filesystemTable.fnAddData(["<span style=\"display:none;\">" + mpoint + "</span>" + mpoint, "<span style=\"display:none;\">" + type + "</span>" + type, "<span style=\"display:none;\">" + name + "</span>" + name + options_text, "<span style=\"display:none;\">" + percent.toString() + "</span>" + createBar(percent, "barwarn") + inodes_text, "<span style=\"display:none;\">" + free.toString() + "</span><i>(" + formatBytes(free, xml) + ")</i>", "<span style=\"display:none;\">" + used.toString() + "</span><i>(" + formatBytes(used, xml) + ")</i>", "<span style=\"display:none;\">" + size.toString() + "</span><i>(" + formatBytes(size, xml) + ")</i>"]);
                } else {
                    filesystemTable.fnAddData(["<span style=\"display:none;\">" + mpoint + "</span>" + mpoint, "<span style=\"display:none;\">" + type + "</span>" + type, "<span style=\"display:none;\">" + name + "</span>" + name + options_text, "<span style=\"display:none;\">" + percent.toString() + "</span>" + createBar(percent) + inodes_text, "<span style=\"display:none;\">" + free.toString() + "</span><i>(" + formatBytes(free, xml) + ")</i>", "<span style=\"display:none;\">" + used.toString() + "</span><i>(" + formatBytes(used, xml) + ")</i>", "<span style=\"display:none;\">" + size.toString() + "</span><i>(" + formatBytes(size, xml) + ")</i>"]);
                }
            } else  {
                if (!isNaN(threshold) && (percent >= threshold)) {
                    filesystemTable.fnAddData(["<span style=\"display:none;\">" + mpoint + "</span>" + mpoint, "<span style=\"display:none;\">" + type + "</span>" + type, "<span style=\"display:none;\">" + name + "</span>" + name + options_text, "<span style=\"display:none;\">" + percent.toString() + "</span>" + createBar(percent, "barwarn") + inodes_text, "<span style=\"display:none;\">" + free.toString() + "</span><i>(" + formatBytes(free, xml) +  ")</i>", "<span style=\"display:none;\">" + used.toString() + "</span>" + formatBytes(used, xml), "<span style=\"display:none;\">" + size.toString() + "</span><i>(" + formatBytes(size, xml) + ")</i>"]);
                } else {
                    filesystemTable.fnAddData(["<span style=\"display:none;\">" + mpoint + "</span>" + mpoint, "<span style=\"display:none;\">" + type + "</span>" + type, "<span style=\"display:none;\">" + name + "</span>" + name + options_text, "<span style=\"display:none;\">" + percent.toString() + "</span>" + createBar(percent) + inodes_text, "<span style=\"display:none;\">" + free.toString() + "</span><i>(" + formatBytes(free, xml) + ")</i>", "<span style=\"display:none;\">" + used.toString() + "</span>" + formatBytes(used, xml), "<span style=\"display:none;\">" + size.toString() + "</span><i>(" + formatBytes(size, xml) + ")</i>"]);
                }
            }
        } else {
            if (!isNaN(threshold) && (percent >= threshold)) {
                filesystemTable.fnAddData(["<span style=\"display:none;\">" + mpoint + "</span>" + mpoint, "<span style=\"display:none;\">" + type + "</span>" + type, "<span style=\"display:none;\">" + name + "</span>" + name + options_text, "<span style=\"display:none;\">" + percent.toString() + "</span>" + createBar(percent, "barwarn") + inodes_text, "<span style=\"display:none;\">" + free.toString() + "</span>" + formatBytes(free, xml), "<span style=\"display:none;\">" + used.toString() + "</span>" + formatBytes(used, xml), "<span style=\"display:none;\">" + size.toString() + "</span>" + formatBytes(size, xml)]);
            } else {
                filesystemTable.fnAddData(["<span style=\"display:none;\">" + mpoint + "</span>" + mpoint, "<span style=\"display:none;\">" + type + "</span>" + type, "<span style=\"display:none;\">" + name + "</span>" + name + options_text, "<span style=\"display:none;\">" + percent.toString() + "</span>" + createBar(percent) + inodes_text, "<span style=\"display:none;\">" + free.toString() + "</span>" + formatBytes(free, xml), "<span style=\"display:none;\">" + used.toString() + "</span>" + formatBytes(used, xml), "<span style=\"display:none;\">" + size.toString() + "</span>" + formatBytes(size, xml)]);
            }
        }
        if (!isNaN(ignore) && (ignore > 0)) {
            if (ignore == 1) {
                total_used += used;
                total_size += used;
            }
        } else {
            total_used += used;
            total_free += free;
            total_size += size;
        }
        total_usage = round((total_used / total_size) * 100, 2);
    });

    if (!isNaN(threshold) && (total_usage >= threshold)) {
        $("#s_fs_total").html(createBar(total_usage, "barwarn"));
    } else {
        $("#s_fs_total").html(createBar(total_usage));
    }
    $("#s_fs_tfree").html(formatBytes(total_free, xml));
    $("#s_fs_tused").html(formatBytes(total_used, xml));
    $("#s_fs_tsize").html(formatBytes(total_size, xml));
}

/**
 * (re)fill the temperature block with the values from the given xml<br><br>
 * build the block content for the temperature block, this includes normal temperature information in the XML
 * and also the HDDTemp information, if there are no information the entire table will be removed
 * to avoid HTML warnings
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshTemp(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('temperature', blocks) < 0))) {
        $("#temperature").remove();
        return;
    }

    var values = false;
    $("#temperatureTable tbody").empty();
    $("MBInfo Temperature Item", xml).each(function getTemperatures(id) {
        var label = "", value = "", limit = 0, _limit = "", event = "";
        label = $(this).attr("Label");
        value = $(this).attr("Value");
        limit = parseFloat($(this).attr("Max"));
        if (isFinite(limit))
            _limit = formatTemp(limit, xml);
        event = $(this).attr("Event");
        if (event !== undefined)
            label += " <img style=\"vertical-align: middle; width:16px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\""+event+"\"/>";
        $("#temperatureTable tbody").append("<tr><td>" + label + "</td><td class=\"right\">" + formatTemp(value, xml) + "</td><td class=\"right\">" + _limit + "</td></tr>");
        values = true;
    });
    if (values) {
        $("#temperature").show();
    } else {
        $("#temperature").hide();
    }
}

/**
 * (re)fill the voltage block with the values from the given xml<br><br>
 * build the voltage information into a separate block, if there is no voltage information available the
 * entire table will be removed to avoid HTML warnings
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshVoltage(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('voltage', blocks) < 0))) {
        $("#voltage").remove();
        return;
    }

    var values = false;
    $("#voltageTable tbody").empty();
    $("MBInfo Voltage Item", xml).each(function getVoltages(id) {
        var label = "", value = 0, max = 0, min = 0, _min = "", _max = "", event = "";
        label = $(this).attr("Label");
        value = parseFloat($(this).attr("Value"));
        max = parseFloat($(this).attr("Max"));
        if (isFinite(max))
            _max = round(max, 2) + "&nbsp;" + genlang(62);
        min = parseFloat($(this).attr("Min"));
        if (isFinite(min))
            _min = round(min, 2) + "&nbsp;" + genlang(62);
        event = $(this).attr("Event");
        if (event !== undefined)
            label += " <img style=\"vertical-align: middle; width:16px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\""+event+"\"/>";
        $("#voltageTable tbody").append("<tr><td>" + label + "</td><td class=\"right\">" + round(value, 2) + "&nbsp;" + genlang(62) + "</td><td class=\"right\">" + _min + "</td><td class=\"right\">" + _max + "</td></tr>");
        values = true;
    });
    if (values) {
        $("#voltage").show();
    } else {
        $("#voltage").hide();
    }
}

/**
 * (re)fill the fan block with the values from the given xml<br><br>
 * build the fan information into a separate block, if there is no fan information available the
 * entire table will be removed to avoid HTML warnings
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshFans(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('fans', blocks) < 0))) {
        $("#fans").remove();
        return;
    }

    var values = false;
    $("#fansTable tbody").empty();
    $("MBInfo Fans Item", xml).each(function getFans(id) {
        var label = "", value = 0, min = 0, _min = "", event = "";
        label = $(this).attr("Label");
        value = parseFloat($(this).attr("Value"));
        min = parseFloat($(this).attr("Min"));
        if (isFinite(min))
            _min = round(min,0) + "&nbsp;" + genlang(63);
        event = $(this).attr("Event");
        if (event !== undefined)
            label += " <img style=\"vertical-align: middle; width:16px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\""+event+"\"/>";
        $("#fansTable tbody").append("<tr><td>" + label + "</td><td class=\"right\">" + round(value,0) + "&nbsp;" + genlang(63) + "</td><td class=\"right\">" + _min + "</td></tr>");
        values = true;
    });
    if (values) {
        $("#fans").show();
    } else {
        $("#fans").hide();
    }
}

/**
 * (re)fill the power block with the values from the given xml<br><br>
 * build the power information into a separate block, if there is no power information available the
 * entire table will be removed to avoid HTML warnings
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshPower(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('power', blocks) < 0))) {
        $("#power").remove();
        return;
    }

    var values = false;
    $("#powerTable tbody").empty();
    $("MBInfo Power Item", xml).each(function getPowers(id) {
        var label = "", value = "", limit = 0, _limit = "", event = "";
        label = $(this).attr("Label");
        value = $(this).attr("Value");
        limit = parseFloat($(this).attr("Max"));
        if (isFinite(limit))
            _limit = round(limit, 2) + "&nbsp;" + genlang(103);
        event = $(this).attr("Event");
        if (event !== undefined)
            label += " <img style=\"vertical-align: middle; width:16px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\""+event+"\"/>";
        $("#powerTable tbody").append("<tr><td>" + label + "</td><td class=\"right\">" + round(value, 2) + "&nbsp;" + genlang(103) + "</td><td class=\"right\">" + _limit + "</td></tr>");
        values = true;
    });
    if (values) {
        $("#power").show();
    } else {
        $("#power").hide();
    }
}

/**
 * (re)fill the current block with the values from the given xml<br><br>
 * build the current information into a separate block, if there is no current information available the
 * entire table will be removed to avoid HTML warnings
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshCurrent(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('current', blocks) < 0))) {
        $("#current").remove();
        return;
    }

    var values = false;
    $("#currentTable tbody").empty();
    $("MBInfo Current Item", xml).each(function getCurrents(id) {
        var label = "", value = "", min = 0, max = 0, _min = "", _max = "", event = "";
        label = $(this).attr("Label");
        value = $(this).attr("Value");

        max = parseFloat($(this).attr("Max"));
        if (isFinite(max))
            _max = round(max, 2) + "&nbsp;" + genlang(106);
        min = parseFloat($(this).attr("Min"));
        if (isFinite(min))
            _min = round(min, 2) + "&nbsp;" + genlang(106);

        event = $(this).attr("Event");
        if (event !== undefined)
            label += " <img style=\"vertical-align: middle; width:16px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\""+event+"\"/>";
        $("#currentTable tbody").append("<tr><td>" + label + "</td><td class=\"right\">" + round(value, 2) + "&nbsp;" + genlang(106) + "</td><td class=\"right\">" + _min + "</td><td class=\"right\">" + _max + "</td></tr>");
        values = true;
    });
    if (values) {
        $("#current").show();
    } else {
        $("#current").hide();
    }
}

/**
 * (re)fill the other block with the values from the given xml<br><br>
 * build the other information into a separate block, if there is no other information available the
 * entire table will be removed to avoid HTML warnings
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshOther(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('other', blocks) < 0))) {
        $("#other").remove();
        return;
    }

    var values = false;
    $("#otherTable tbody").empty();
    $("MBInfo Other Item", xml).each(function getOthers(id) {
        var label = "", value = "", event = "";
        label = $(this).attr("Label");
        value = $(this).attr("Value");

        event = $(this).attr("Event");
        if (event !== undefined)
            label += " <img style=\"vertical-align: middle; width:16px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\""+event+"\"/>";
        $("#otherTable tbody").append("<tr><td>" + label + "</td><td class=\"right\">" + value + "</td></tr>");
        values = true;
    });
    if (values) {
        $("#other").show();
    } else {
        $("#other").hide();
    }
}

/**
 * (re)fill the ups block with the values from the given xml<br><br>
 * build the ups information into a separate block, if there is no ups information available the
 * entire table will be removed to avoid HTML warnings
 * @param {jQuery} xml phpSysInfo-XML
 */
function refreshUps(xml) {
    if ((blocks.length <= 0) || ((blocks[0] !== "true") && ($.inArray('ups', blocks) < 0))) {
        $("#ups").remove();
        return;
    }

    var add_apcupsd_cgi_links = ($("[ApcupsdCgiLinks='1']", xml).length > 0);
    var html = "", tree = [], closed = [], index = 0, values = false;
    html += "<h2>" + genlang(68) + "</h2>\n";
    html += "        <div style=\"overflow-x:auto;\">\n";
    html += "          <table class=\"tablemain\" id=\"UPSTree\">\n";
    html += "            <tbody class=\"tree\">\n";

    $("#ups").empty();
    $("UPSInfo UPS", xml).each(function getUps(id) {
        var name = "", model = "", mode = "", start_time = "", upsstatus = "", temperature = "", outages_count = "", last_outage = "", last_outage_finish = "", line_voltage = "", line_frequency = "", load_percent = "", battery_date = "", battery_voltage = "", battery_charge_percent = "", time_left_minutes = "";
        name = $(this).attr("Name");
        model = $(this).attr("Model");
        mode = $(this).attr("Mode");
        start_time = $(this).attr("StartTime");
        upsstatus = $(this).attr("Status");

        temperature = $(this).attr("Temperature");
        outages_count = $(this).attr("OutagesCount");
        last_outage = $(this).attr("LastOutage");
        last_outage_finish = $(this).attr("LastOutageFinish");
        line_voltage = $(this).attr("LineVoltage");
        line_frequency = $(this).attr("LineFrequency");
        load_percent = parseInt($(this).attr("LoadPercent"), 10);
        battery_date = $(this).attr("BatteryDate");
        battery_voltage = $(this).attr("BatteryVoltage");
        battery_charge_percent = parseInt($(this).attr("BatteryChargePercent"), 10);
        time_left_minutes = $(this).attr("TimeLeftMinutes");

        if (mode !== undefined) {
            html += "<tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespanbold\">" + name + " (" + mode + ")</span></div></td></tr>\n";
        } else {
            html += "<tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespanbold\">" + name + "</span></div></td></tr>\n";
        }
        index = tree.push(0);
        if (model !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(70) + "</span></div></td><td>" + model + "</td></tr>\n";
            tree.push(index);
        }
        if (start_time !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(72) + "</span></div></td><td>" + start_time + "</td></tr>\n";
            tree.push(index);
        }
        if (upsstatus !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(73) + "</span></div></td><td>" + upsstatus + "</td></tr>\n";
            tree.push(index);
        }
        if (temperature !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(84) + "</span></div></td><td>" + temperature + "</td></tr>\n";
            tree.push(index);
        }
        if (outages_count !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(74) + "</span></div></td><td>" + outages_count + "</td></tr>\n";
            tree.push(index);
        }
        if (last_outage !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(75) + "</span></div></td><td>" + last_outage + "</td></tr>\n";
            tree.push(index);
        }
        if (last_outage_finish !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(76) + "</span></div></td><td>" + last_outage_finish + "</td></tr>\n";
            tree.push(index);
        }
        if (line_voltage !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(77) + "</span></div></td><td>" + line_voltage + "&nbsp;" + genlang(82) + "</td></tr>\n";
            tree.push(index);
        }
        if (line_frequency !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(108) + "</span></div></td><td>" + line_frequency + "&nbsp;" + genlang(109) + "</td></tr>\n";
            tree.push(index);
        }
        if (!isNaN(load_percent)) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(78) + "</span></div></td><td>" + createBar(load_percent) + "</td></tr>\n";
            tree.push(index);
        }
        if (battery_date !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(104) + "</span></div></td><td>" + battery_date + "</td></tr>\n";
            tree.push(index);
        }
        if (battery_voltage !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(79) + "</span></div></td><td>" + battery_voltage + "&nbsp;" + genlang(82) + "</td></tr>\n";
            tree.push(index);
        }
        if (!isNaN(battery_charge_percent)) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(80) + "</span></div></td><td>" + createBar(battery_charge_percent) + "</td></tr>\n";
            tree.push(index);
        }
        if (time_left_minutes !== undefined) {
            html += "<tr><td style=\"width:160px\"><div class=\"treediv\"><span class=\"treespan\">" + genlang(81) + "</span></div></td><td>" + time_left_minutes + "&nbsp;" + genlang(83) + "</td></tr>\n";
            tree.push(index);
        }
        values=true;
    });
    html += "            </tbody>\n";
    html += "          </table>\n";
    html += "        </div>\n";
    if (add_apcupsd_cgi_links){
        html += " (<a title='details' href='/cgi-bin/apcupsd/multimon.cgi' target='apcupsdcgi'>" + genlang(99) + "</a>)\n";
    }

    $("#ups").append(html);

    if (values) {
        $("#UPSTree").jqTreeTable(tree, {
            openImg: "./gfx/treeTable/tv-collapsable.gif",
            shutImg: "./gfx/treeTable/tv-expandable.gif",
            leafImg: "./gfx/treeTable/tv-item.gif",
            lastOpenImg: "./gfx/treeTable/tv-collapsable-last.gif",
            lastShutImg: "./gfx/treeTable/tv-expandable-last.gif",
            lastLeafImg: "./gfx/treeTable/tv-item-last.gif",
            vertLineImg: "./gfx/treeTable/vertline.gif",
            blankImg: "./gfx/treeTable/blank.gif",
            collapse: closed,
            column: 0,
            striped: true,
            highlight: false,
            state: false
        });
        $("#ups").show();
    } else {
        $("#ups").hide();
    }
}

/**
 * reload the page, this means all values are refreshed
 */
function reload(initiate) {
    $.ajax({
        url: 'xml.php',
        dataType: 'xml',
        error: function error() {
            if ((typeof(initiate) === 'boolean') && (initiate === true)) {
                $.jGrowl("Error loading XML document!", {
                    sticky: true
                });
            } else {
                $.jGrowl("Error loading XML document!");
            }
        },
        success: function buildblocks(xml) {
            if ((typeof(initiate) === 'boolean') && (initiate === true)) {
                populateErrors(xml);
            }

            refreshVitals(xml);
            refreshHardware(xml);
            refreshMemory(xml);
            refreshFilesystems(xml);
            refreshNetwork(xml);
            refreshVoltage(xml);
            refreshCurrent(xml);
            refreshTemp(xml);
            refreshFans(xml);
            refreshPower(xml);
            refreshOther(xml);
            refreshUps(xml);
            changeLanguage();

            if ((typeof(initiate) === 'boolean') && (initiate === true)) {
                displayPage(xml);
                settimer(xml);
            } else {
                for (var i = 0; i < plugin_liste.length; i++) {
                    try {
                        //dynamic call
                        window[plugin_liste[i].toLowerCase() + '_request']();
                    }
                    catch (err) {
                    }
                }
            }

            $('.stripeMe tr:nth-child(even)').addClass('even');
        }
    });
}

/**
 * set a reload timer for the page
 * @param {jQuery} xml phpSysInfo-XML
 */
function settimer(xml) {
    $("Options", xml).each(function getRefreshTime(id) {
        var options, refresh = "";
        options = $("Options", xml).get(id);
        refresh = $(this).attr("refresh");
        if (refresh !== '0') {
            $.timer(refresh, reload);
        }
    });
}

$(document).ready(function buildpage() {
    var i = 0, old_template = null, cookie_template = null, cookie_language = null, blocktmp = "";

    showCPUListExpanded = $("#showCPUListExpanded").val().toString()==="true";
    showCPUInfoExpanded = $("#showCPUInfoExpanded").val().toString()==="true";
    showNetworkInfosExpanded = $("#showNetworkInfosExpanded").val().toString()==="true";
    showMemoryInfosExpanded = $("#showMemoryInfosExpanded").val().toString()==="true";
    showCPULoadCompact = $("#showCPULoadCompact").val().toString()==="true";
    switch ($("#showNetworkActiveSpeed").val().toString()) {
        case "bps":  showNetworkActiveSpeed = 2;
                      break;
        case "true": showNetworkActiveSpeed = 1;
                      break;
        default:     showNetworkActiveSpeed = 0;
    }

    blocktmp = $("#blocks").val().toString();
    if (blocktmp.length >0 ){
        if (blocktmp === "true") {
            blocks[0] = "true";
        } else {
            blocks = blocktmp.split(',');
            var j = 2;
            for (i = 0; i < blocks.length; i++) {
                if ($("#"+blocks[i]).length > 0) {
                    $("#output").children().eq(j).before($("#"+blocks[i]));
                    j++;
                }
            }

        }
    }

    if ($("#language option").length < 2) {
        current_language = $("#language").val().toString();
/*
        changeLanguage();
        for (i = 0; i < plugin_liste.length; i++) {
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
/*
        changeLanguage();
        for (i = 0; i < plugin_liste.length; i++) {
            changeLanguage(plugin_liste[i]);
        }
*/
        $('#language').show();
        $('span[class=lang_045]').show();
        $("#language").change(function changeLang() {
            var i = 0;
            current_language = $("#language").val().toString();
            createCookie('psi_language', current_language, 365);
            changeLanguage();
            for (i = 0; i < plugin_liste.length; i++) {
                changeLanguage(plugin_liste[i]);
            }
            return false;
        });
    }
    if ($("#template option").length < 2) {
        switchStyle($("#template").val().toString());
    } else {
        cookie_template = readCookie("psi_template");
        if (cookie_template !== null) {
            old_template = $("#template").val();
            $("#template").val(cookie_template);
            if ($("#template").val() === null) {
                $("#template").val(old_template);
            }           
        }
        switchStyle($("#template").val().toString());
        $('#template').show();
        $('span[class=lang_044]').show();
        $("#template").change(function changeTemplate() {
            switchStyle($("#template").val().toString());
            createCookie('psi_template', $("#template").val().toString(), 365);
            return false;
        });
    }

    filesystemtable();

    reload(true);

    $("#errors").nyroModal();
});

jQuery.fn.dataTableExt.oSort['span-string-asc'] = function sortStringAsc(a, b) {
    var x = "", y = "";
    x = a.substring(a.indexOf(">") + 1, a.indexOf("</"));
    y = b.substring(b.indexOf(">") + 1, b.indexOf("</"));
    return ((x < y) ? -1 : ((x > y) ? 1 : 0));
};

jQuery.fn.dataTableExt.oSort['span-string-desc'] = function sortStringDesc(a, b) {
    var x = "", y = "";
    x = a.substring(a.indexOf(">") + 1, a.indexOf("</"));
    y = b.substring(b.indexOf(">") + 1, b.indexOf("</"));
    return ((x < y) ? 1 : ((x > y) ? -1 : 0));
};

jQuery.fn.dataTableExt.oSort['span-number-asc'] = function sortNumberAsc(a, b) {
    var x = 0, y = 0;
    x = parseInt(a.substring(a.indexOf(">") + 1, a.indexOf("</")), 10);
    y = parseInt(b.substring(b.indexOf(">") + 1, b.indexOf("</")), 10);
    return ((x < y) ? -1 : ((x > y) ? 1 : 0));
};

jQuery.fn.dataTableExt.oSort['span-number-desc'] = function sortNumberDesc(a, b) {
    var x = 0, y = 0;
    x = parseInt(a.substring(a.indexOf(">") + 1, a.indexOf("</")), 10);
    y = parseInt(b.substring(b.indexOf(">") + 1, b.indexOf("</")), 10);
    return ((x < y) ? 1 : ((x > y) ? -1 : 0));
};

jQuery.fn.dataTableExt.oSort['span-ip-asc'] = function sortIpAsc(a, b) {
    var x = 0, y = 0, aa = "", bb = "";
    aa = a.substring(a.indexOf(">") + 1, a.indexOf("</"));
    bb = b.substring(b.indexOf(">") + 1, b.indexOf("</"));
    x = full_addr(aa);
    y = full_addr(bb);
    if ((x === '') || (y === '')) {
        x = aa;
        y = bb;
    }
    return ((x < y) ? -1 : ((x > y) ? 1 : 0));
};

jQuery.fn.dataTableExt.oSort['span-ip-desc'] = function sortIpDesc(a, b) {
    var x = 0, y = 0, aa = "", bb = "";
    aa = a.substring(a.indexOf(">") + 1, a.indexOf("</"));
    bb = b.substring(b.indexOf(">") + 1, b.indexOf("</"));
    x = full_addr(aa);
    y = full_addr(bb);
    if ((x === '') || (y === '')) {
        x = aa;
        y = bb;
    }
    return ((x < y) ? 1 : ((x > y) ? -1 : 0));
};

function full_addr(ip_string) {
    var wrongvalue = false;
    ip_string = $.trim(ip_string).toLowerCase();
    // ipv4 notation
    if (ip_string.match(/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$)/)) {
        ip_string ='::ffff:' + ip_string;
    }
    // replace ipv4 address if any
    var ipv4 = ip_string.match(/(.*:)([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$)/);
    if (ipv4) {
        ip_string = ipv4[1];
        ipv4 = ipv4[2].match(/[0-9]+/g);
        for (var i = 0;i < 4;i ++) {
            var byte = parseInt(ipv4[i],10);
            if (byte<256) {
                ipv4[i] = ("0" + byte.toString(16)).substr(-2);
            } else {
                wrongvalue = true;
                break;
            }
        }
        if (wrongvalue) {
            ip_string = '';
        } else {
            ip_string += ipv4[0] + ipv4[1] + ':' + ipv4[2] + ipv4[3];
        }
    }

    if (ip_string === '') {
        return '';
    }
    // take care of leading and trailing ::
    ip_string = ip_string.replace(/^:|:$/g, '');

    var ipv6 = ip_string.split(':');

    for (var li = 0; li < ipv6.length; li ++) {
        var hex = ipv6[li];
        if (hex !== "") {
            if (!hex.match(/^[0-9a-f]{1,4}$/)) {
                wrongvalue = true;
                break;
            }
            // normalize leading zeros
            ipv6[li] = ("0000" + hex).substr(-4);
        }
        else {
            // normalize grouped zeros ::
            hex = [];
            for (var j = ipv6.length; j <= 8; j ++) {
                hex.push('0000');
            }
            ipv6[li] = hex.join(':');
        }
    }
    if (!wrongvalue) {
        var out = ipv6.join(':');
        if (out.length == 39) {
            return out;
        } else {
            return '';
        }
    } else {
        return '';
    }
}

/**
 * generate the block element for a specific plugin that is available
 * @param {String} plugin name of the plugin
 * @param {Number} translationid id of the translated headline in the plugin translation file
 * @param {Boolean} reload controls if a reload button should be appended to the headline
 * @return {String} HTML string which contains the full layout of the block
 */
function buildBlock(plugin, translationid, reload) {
    var block = "", reloadpic = "";
    if (reload) {
        reloadpic = "<img id=\"Reload_" + plugin + "Table\" src=\"./gfx/reload.gif\" alt=\"reload\" title=\"reload\" style=\"vertical-align:middle;float:right;cursor:pointer;border:0px;width:16px\" />&nbsp;";
    }
    block += "<div id=\"panel_" + plugin + "\" style=\"display:none;\">\n";
    block += "<div id=\"Plugin_" + plugin + "\" class=\"plugin\" style=\"display:none;\">\n";
    block += "<h2>" + reloadpic + genlang(translationid, plugin) + "</h2>\n";
    block += "</div>\n";
    block += "</div>\n";
    return block;
}

/**
 * translate a plugin and add this plugin to the internal plugin-list, this is only needed once and shouldn't be called more than once
 * @param {String} plugin name of the plugin  that should be translated
 */
function plugin_translate(plugin) {
    plugin_liste.pushIfNotExist(plugin);
    changeLanguage(plugin);
}

/**
 * generate a formatted datetime string of the current datetime
 * @return {String} formatted datetime string
 */
function datetime() {
    var date, day = 0, month = 0, year = 0, hour = 0, minute = 0, days = "", months = "", years = "", hours = "", minutes = "";
    date = new Date();
    day = date.getDate();
    month = date.getMonth() + 1;
    year = date.getFullYear();
    hour = date.getHours();
    minute = date.getMinutes();

    // format values smaller that 10 with a leading 0
    days = (day < 10) ? "0" + day.toString() : day.toString();
    months = (month < 10) ? "0" + month.toString() : month.toString();
    years = (year < 1000) ? year.toString() : year.toString();
    minutes = (minute < 10) ? "0" + minute.toString() : minute.toString();
    hours = (hour < 10) ? "0" + hour.toString() : hour.toString();

    return days + "." + months + "." + years + " - " + hours + ":" + minutes;
}

Array.prototype.pushIfNotExist = function(val) {
    if (typeof(val) == 'undefined' || val === '') {
        return;
    }
    val = $.trim(val);
    if ($.inArray(val, this) == -1) {
        this.push(val);
    }
};

/**
 * insert dynamically a js script file into the website
 * @param {String} name name of the script that should be included
 */
/*
function appendjs(name) {
    var scrptE, hdEl;
    scrptE = document.createElement("script");
    hdEl = document.getElementsByTagName("head")[0];
    scrptE.setAttribute("src", name);
    scrptE.setAttribute("type", "text/javascript");
    hdEl.appendChild(scrptE);
}
*/
/**
 * insert dynamically a css file into the website
 * @param {String} name name of the css file that should be included
 */
/*
function appendcss(name) {
    var scrptE, hdEl;
    scrptE = document.createElement("link");
    hdEl = document.getElementsByTagName("head")[0];
    scrptE.setAttribute("type", "text/css");
    scrptE.setAttribute("rel", "stylesheet");
    scrptE.setAttribute("href", name);
    hdEl.appendChild(scrptE);
}
*/
