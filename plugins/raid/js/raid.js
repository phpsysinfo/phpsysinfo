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
// $Id: raid.js 679 2012-09-04 10:10:11Z namiltd $
//

/*global $, jQuery, buildBlock, genlang, createBar, plugin_translate, datetime */

"use strict";

var raid_show = false;

/**
 * get the details of the raid
 * @param {jQuery} xml part of the plugin-XML
 * @param {number} id id of the device
 */
function raid_buildinfos(xml, id) {
    var html = "", prog = "", devname = "", devstatus = "", devlevel = "", devcontroller = "", devbattery = "", devsupported = "", devsize = 0, devstride = 0, devsubsets = 0, devdevs = 0, devspares = 0, devchunk = 0, devalgor = "", devpersist = 0, devreg = 0, devact = 0, devcache = 0, devbad = 0, devread = "", devwrite = "", button = "";

    prog = $(xml).attr("Program");
    devname = $(xml).attr("Name");
    devstatus = $(xml).attr("Status");
    devlevel = $(xml).attr("Level");
    devcontroller = $(xml).attr("Controller");
    devbattery = $(xml).attr("Battery");
    devsupported = $(xml).attr("Supported");
    devsize = parseInt($(xml).attr("Size"), 10);
    devstride = parseInt($(xml).attr("Stride"), 10);
    devsubsets = parseInt($(xml).attr("Subsets"), 10);
    devdevs = parseInt($(xml).attr("Devs"), 10);
    devspares = parseInt($(xml).attr("Spares"), 10);
    devchunk = parseInt($(xml).attr("Chunk_Size"), 10);
    devalgor = $(xml).attr("Algorithm");
    devpersist = parseInt($(xml).attr("Persistend_Superblock"), 10);
    devreg = parseInt($(xml).attr("Disks_Registered"), 10);
    devact = parseInt($(xml).attr("Disks_Active"), 10);
    devcache = parseInt($(xml).attr("Cache_Size"), 10);
    devbad = parseInt($(xml).attr("Bad_Blocks"), 10);
    devread = $(xml).attr("ReadPolicy");
    devwrite = $(xml).attr("WritePolicy");
    html += "<tr><td>" + genlang(22, "Raid") + "</td><td>" + prog + "</td></tr>";
    if (devname !== undefined) html += "<tr><td>" + genlang(3, "Raid") + "</td><td>" + devname + "</td></tr>";
    html += "<tr><td>" + genlang(4, "Raid") + "</td><td>" + devstatus + "</td></tr>";
    if (devlevel !== undefined) html += "<tr><td>" + genlang(5, "Raid") + "</td><td>" + devlevel + "</td></tr>";
    if (!isNaN(devsize)) html += "<tr><td>" + genlang(6, "Raid") + "</td><td>" + formatBytes(devsize, xml) + "</td></tr>";
    if (!isNaN(devstride)) html += "<tr><td>" + genlang(7, "Raid") + "</td><td>" + devstride + "</td></tr>";
    if (!isNaN(devsubsets)) html += "<tr><td>" + genlang(8, "Raid") + "</td><td>" + devsubsets + "</td></tr>";
    if (!isNaN(devdevs)) html += "<tr><td>" + genlang(9, "Raid") + "</td><td>" + devdevs + "</td></tr>";
    if (!isNaN(devspares)) html += "<tr><td>" + genlang(10, "Raid") + "</td><td>" + devspares + "</td></tr>";

    if (!isNaN(devchunk)) html += "<tr><td>" + genlang(13, "Raid") + "</td><td>" + devchunk + "K</td></tr>";
    if (devalgor !== undefined) html += "<tr><td>" + genlang(14, "Raid") + "</td><td>" + devalgor + "</td></tr>";
    if (!isNaN(devpersist)) {
        if (devpersist == 1) {
            html += "<tr><td>" + genlang(15, "Raid") + "</td><td>" + genlang(16, "Raid") + "</td></tr>";
        } else {
            html += "<tr><td>" + genlang(15, "Raid") + "</td><td>" + genlang(17, "Raid") + "</td></tr>";
        }
    }
    if (!isNaN(devreg) && !isNaN(devact)) html += "<tr><td>" + genlang(18, "Raid") + "</td><td>" + devreg + "/" + devact + "</td></tr>";
    if (devcontroller !== undefined) html += "<tr><td>" + genlang(19, "Raid") + "</td><td>" + devcontroller + "</td></tr>";
    if (devbattery !== undefined) html += "<tr><td>" + genlang(20, "Raid") + "</td><td>" + devbattery + "</td></tr>";
    if (devsupported !== undefined) html += "<tr><td>" + genlang(21, "Raid") + "</td><td>" + devsupported + "</td></tr>";
    if (devread !== undefined) html += "<tr><td>" + genlang(23, "Raid") + "</td><td>" + devread + "</td></tr>";
    if (devwrite !== undefined) html += "<tr><td>" + genlang(24, "Raid") + "</td><td>" + devwrite + "</td></tr>";
    if (!isNaN(devcache)) html += "<tr><td>" + genlang(25, "Raid") + "</td><td>" + formatBytes(devcache, xml) + "</td></tr>";
    if (!isNaN(devbad)) html += "<tr><td>" + genlang(26, "Raid") + "</td><td>" + devbad + "</td></tr>";
    
    button += "<h3 style=\"cursor:pointer\" id=\"sPlugin_Raid_Info" + id + "\"><img src=\"./gfx/bullet_toggle_plus.gif\" alt=\"plus\" title=\"\" style=\"vertical-align:middle;width:16px;\" />" + genlang(2, "Raid") + "</h3>";
    button += "<h3 style=\"cursor:pointer; display:none;\" id=\"hPlugin_Raid_Info" + id + "\"><img src=\"./gfx/bullet_toggle_minus.gif\" alt=\"minus\" title=\"\" style=\"vertical-align:middle;width:16px;\" />" + genlang(2, "Raid") + "</h3>";
    button += "<table id=\"Plugin_Raid_InfoTable" + id + "\" style=\"border:none; border-collapse:collapse; display:none;\"><tbody>" + html + "</tbody></table>";
    return button;
}

/**
 * generate a html string with the current action on the disks
 * @param {jQuery} xml part of the plugin-XML
 */
function raid_buildaction(xml) {
    var html = "", name = "", time = "", tunit = "", percent = 0;
    $("Action", xml).each(function mdstatus_getaction(id) {
        name = $(this).attr("Name");
        if (parseInt(name, 10) !== -1) {
            time = $(this).attr("Time_To_Finish");
            tunit = $(this).attr("Time_Unit");
            percent = parseFloat($(this).attr("Percent"));
            html += "<div style=\"padding-left:10px;\">";
            html += genlang(11, "Raid") + ":&nbsp;" + name + "<br>";
            html += createBar(percent);
            if ((time !== undefined) && (tunit !== undefined)) {
                html += "<br>";
                html += genlang(12, "Raid") + ":&nbsp;" + time + "&nbsp;" + tunit;
            }
            html += "</div>";
        }
    });
    return html;
}

/**
 * choose the right diskdrive icon
 * @param {jQuery} xml part of the plugin-XML
 */
function raid_diskicon(xml, id) {
    $("RaidItems Item", xml).each(function raid_getitems(itemid) {
        var status = "", name = "", type = "", info = "", parentid = 0;

        status = $(this).attr("Status");
        name = $(this).attr("Name");
        type = $(this).attr("Type");
        info = $(this).attr("Info");
        if (info === undefined) info = "";
        parentid = parseInt($(this).attr("ParentID"), 10);

        var img = "", alt = "", bcolor = "";
        switch (status) {
        case "ok":
            img = "harddriveok.png";
            alt = "ok";
            bcolor = "green";
            break;
        case "F":
            img = "harddrivefail.png";
            alt = "fail";
            bcolor = "red";
            break;
        case "U":
            img = "harddriveunc.png";
            alt = "unconfigured";
            bcolor = "purple";
            break;
        case "S":
            img = "harddrivespare.png";
            alt = "spare";
            bcolor = "gray";
            break;
        case "W":
            img = "harddrivewarn.png";
            alt = "warning";
            bcolor = "orange";
            break;
        default:
//            alert("--" + diskstatus + "--");
            img = "error.png";
            alt = "error";

            break;
        }

        if (!isNaN(parentid)) {
            if (type === "disk") {
                $("#Plugin_Raid_Item" + id + "-" + parentid).append("<div class=\"plugin_raid_biun\" title=\"" + info + "\"><img src=\"./plugins/raid/gfx/" + img + "\" alt=\"" + alt + "\" style=\"width:60px;height:60px;\" onload=\"PNGload($(this));\" /><br><small>" + name + "</small></div>"); //onload IE6 PNG fix
            } else {
                if (parentid === 0) {
                    $("#Plugin_Raid_List-" + id).append("<div class=\"plugin_raid_item\" id=\"Plugin_Raid_Item" + id + "-" + (itemid+1) + "\" style=\"border-color:" + bcolor + "\">" + name + "<br></div>");
                } else {
                    $("#Plugin_Raid_Item" + id + "-" + parentid).append("<div class=\"plugin_raid_item\" id=\"Plugin_Raid_Item" + id + "-" + (itemid+1) + "\" style=\"border-color:" + bcolor + "\">" + name + "<br></div>");
                } 
            }
        }
    });
}

/**
 * fill the plugin block
 * @param {jQuery} xml plugin-XML
 */
function raid_populate(xml) {
    $("#Plugin_RaidTable").empty();
    $("#Plugin_RaidTable").append("<tbody>");
    var arr = $("Plugins Plugin_Raid Raid", xml);
    arr.each(function raid_getdevice(id) {
        var htmldisks = "", buildedaction = "";
        htmldisks += "<table style=\"border:none; width:100%;\"><tbody>";
        htmldisks += "<tr><td id=\"Plugin_Raid_List-" + id + "\"></td></tr>";
        buildedaction = raid_buildaction($(this));
        if (buildedaction) {
            htmldisks += "<tr><td>" + buildedaction + "</td></tr>";
        }
        htmldisks += "<tr><td>" + raid_buildinfos($(this), id);
        /*if (id != (arr.length - 1)) { // not last element
            htmldisks += "<br>";
        }*/
        htmldisks += "</td></tr>";        
        htmldisks += "</tbody></table>";

        $("#Plugin_RaidTable").append("<tr><td><br>" + $(this).attr("Device_Name") + "</td><td>" + htmldisks + "</td></tr>");
        raid_diskicon(this, id);

        $("#sPlugin_Raid_Info" + id).click(function raid_showinfo() {
            $("#Plugin_Raid_InfoTable" + id).slideDown("fast");
            $("#sPlugin_Raid_Info" + id).hide();
            $("#hPlugin_Raid_Info" + id).show();
        });
        $("#hPlugin_Raid_Info" + id).click(function raid_hideinfo() {
            $("#Plugin_Raid_InfoTable" + id).slideUp("fast");
            $("#hPlugin_Raid_Info" + id).hide();
            $("#sPlugin_Raid_Info" + id).show();
        });
        raid_show = true;
    });

    $("#Plugin_RaidTable").append("</tbody>");
}

/**
 * load the xml via ajax
 */
function raid_request() {
    $("#Reload_RaidTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=Raid",
        dataType: "xml",
        error: function raid_error() {
            $.jGrowl("Error loading XML document for Plugin Raid");
        },
        success: function raid_buildblock(xml) {
            populateErrors(xml);
            raid_populate(xml);
            if (raid_show) {
                plugin_translate("Raid");
                $("#Plugin_Raid").show();
            }
        }
    });
}

$(document).ready(function raid_buildpage() {
    var html = "";

    $("#footer").before(buildBlock("Raid", 1, true));
    html += "        <div style=\"overflow-x:auto;\">\n";
    html += "          <table id=\"Plugin_RaidTable\" style=\"border-collapse:collapse;\">\n";
    html += "          </table>\n";
    html += "        </div>\n";
    $("#Plugin_Raid").append(html);

    $("#Plugin_Raid").css("width", "915px");

    raid_request();

    $("#Reload_RaidTable").click(function raid_reload(id) {
        raid_request();
        $(this).attr("title", datetime());
    });
});
