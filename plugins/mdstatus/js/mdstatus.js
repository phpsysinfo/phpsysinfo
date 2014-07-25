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
// $Id: mdstatus.js 679 2012-09-04 10:10:11Z namiltd $
//

/*global $, jQuery, buildBlock, genlang, createBar, plugin_translate, datetime */

"use strict";

//appendcss("./plugins/MDStatus/css/MDStatus.css");

var mdstatus_show = false;

/**
 * get the details of the raid
 * @param {jQuery} xml part of the plugin-XML
 * @param {number} id id of the device
 */
function mdstatus_buildinfos(xml, id) {
    var html = "", devstatus = "", devlevel = "", devchunk = 0, devsuper = 0, devalgo = 0, devactive = 0, devregis = 0, button = "";

    devstatus = $(xml).attr("Disk_Status");
    devlevel = $(xml).attr("Level");
    devchunk = parseInt($(xml).attr("Chunk_Size"), 10);
    devsuper = parseInt($(xml).attr("Persistent_Superblock"), 10);
    devalgo = parseInt($(xml).attr("Algorithm"), 10);
    devactive = parseInt($(xml).attr("Disks_Active"), 10);
    devregis = parseInt($(xml).attr("Disks_Registered"), 10);
    html += "<tr><td>" + genlang(5, false, "MDStatus") + "</td><td>" + devstatus + "</td></tr>";
    html += "<tr><td>" + genlang(6, false, "MDStatus") + "</td><td>" + devlevel + "</td></tr>";
    if (devchunk !== -1) {
        html += "<tr><td>" + genlang(7, false, "MDStatus") + "</td><td>" + devchunk + "K</td></tr>";
    }
    if (devalgo !== -1) {
        html += "<tr><td>" + genlang(8, false, "MDStatus") + "</td><td>" + devalgo + "</td></tr>";
    }
    if (devsuper !== -1) {
        html += "<tr><td>" + genlang(9, false, "MDStatus") + "</td><td>" + genlang(10, false, "MDStatus") + "</td></tr>";
    }
    else {
        html += "<tr><td>" + genlang(9, false, "MDStatus") + "</td><td>" + genlang(11, false, "MDStatus") + "</td></tr>";
    }
    if (devactive !== -1 && devregis !== -1) {
        html += "<tr><td>" + genlang(12, false, "MDStatus") + "</td><td>" + devregis + "/" + devactive + "</td></tr>";
    }
    button += "<h3 style=\"cursor: pointer\" id=\"sPlugin_MDStatus_Info" + id + "\"><img src=\"./gfx/bullet_toggle_plus.png\" alt=\"plus\" style=\"vertical-align:middle;\" />" + genlang(4, false, "MDStatus") + "</h3>";
    button += "<h3 style=\"cursor: pointer; display: none;\" id=\"hPlugin_MDStatus_Info" + id + "\"><img src=\"./gfx/bullet_toggle_minus.png\" alt=\"minus\" style=\"vertical-align:middle;\" />" + genlang(4, false, "MDStatus") + "</h3>";
    button += "<table id=\"Plugin_MDStatus_InfoTable" + id + "\" style=\"border-spacing:0; display:none;\">" + html + "</table>";
    return button;
}

/**
 * generate a html string with the current action on the disks
 * @param {jQuery} xml part of the plugin-XML
 */
function mdstatus_buildaction(xml) {
    var html = "", name = "", time = "", tunit = "", percent = 0;
    $("Action", xml).each(function mdstatus_getaction(id) {
        name = $(this).attr("Name");
        if (parseInt(name, 10) !== -1) {
            time = $(this).attr("Time_To_Finish");
            tunit = $(this).attr("Time_Unit");
            percent = parseFloat($(this).attr("Percent"));
            html += "<div style=\"padding-left:10px;\">";
            html += genlang(13, false, "MDStatus") + ":&nbsp;" + name + "<br/>";
            html += createBar(percent);
            html += "<br/>";
            html += genlang(14, false, "MDStatus") + "&nbsp;" + time + "&nbsp;" + tunit;
            html += "</div>";
        }
    });
    return html;
}

/**
 * choose the right diskdrive icon
 * @param {jQuery} xml part of the plugin-XML
 */
function mdstatus_diskicon(xml) {
    var html = "";
    $("Disks Disk", xml).each(function mdstatus_getdisk(id) {
        var diskstatus = "", diskname = "", img = "", alt = "";
        html += "<div class=\"plugin_mdstatus_biun\">";
        diskstatus = $(this).attr("Status");
        diskname = $(this).attr("Name");
        switch (diskstatus) {
        case " ":
        case "":
            img = "harddriveok.png";
            alt = "ok";
            break;
        case "F":
            img = "harddrivefail.png";
            alt = "fail";
            break;
        case "S":
            img = "harddrivespare.png";
            alt = "spare";
            break;
        default:
            alert("--" + diskstatus + "--");
            img = "error.png";
            alt = "error";
            break;
        }
        html += "<img class=\"plugin_mdstatus_biun\" src=\"./plugins/mdstatus/gfx/" + img + "\" alt=\"" + alt + "\" />";
        html += "<small>" + diskname + "</small>";
        html += "</div>";
    });
    return html;
}

/**
 * fill the plugin block
 * @param {jQuery} xml plugin-XML
 */
function mdstatus_populate(xml) {
    var htmltypes = "";

    $("#Plugin_MDStatusTable").empty();

    $("Plugins Plugin_MDStatus Supported_Types Type", xml).each(function mdstatus_getsupportedtypes(id) {
//        htmltypes += "<li>" + $(this).attr("Name") + "</li>";
        htmltypes += "<b>" + $(this).attr("Name") + " </b>";
    });
    if (htmltypes.length > 0) {
        htmltypes = "<ul>" + htmltypes + "</ul>";
        $("#Plugin_MDStatusTable").append("<tr><td style=\"width:160px;\">" + genlang(2, false, "MDStatus") + "</td><td>" + htmltypes + "</td></tr>");
        mdstatus_show = true;
    }

    $("Plugins Plugin_MDStatus Raid", xml).each(function mdstatus_getdevice(id) {
        var htmldisks = "", htmldisklist = "", topic = "", name = "", buildedaction = "";
        name = $(this).attr("Device_Name");
        htmldisklist += mdstatus_diskicon(this);
        htmldisks += "<table style=\"width:100%;\">";
        htmldisks += "<tr><td>" + htmldisklist + "</td></tr>";
        buildedaction = mdstatus_buildaction($(this));
        if (buildedaction) {
            htmldisks += "<tr><td>" + buildedaction + "</td></tr>";
        }
        htmldisks += "<tr><td>" + mdstatus_buildinfos($(this), id) + "<td></tr>";
        htmldisks += "</table>";
        if (id) {
            topic = "";
        }
        else {
            topic = genlang(3, false, "MDStatus");
        }
        $("#Plugin_MDStatusTable").append("<tr><td>" + topic + "</td><td><div class=\"plugin_mdstatus_biun\" style=\"text-align:left;\"><b>" + name + "</b></div>" + htmldisks + "</td></tr>");
        $("#sPlugin_MDStatus_Info" + id).click(function mdstatus_showinfo() {
            $("#Plugin_MDStatus_InfoTable" + id).slideDown("slow");
            $("#sPlugin_MDStatus_Info" + id).hide();
            $("#hPlugin_MDStatus_Info" + id).show();
        });
        $("#hPlugin_MDStatus_Info" + id).click(function mdstatus_hideinfo() {
            $("#Plugin_MDStatus_InfoTable" + id).slideUp("slow");
            $("#hPlugin_MDStatus_Info" + id).hide();
            $("#sPlugin_MDStatus_Info" + id).show();
        });
        mdstatus_show = true;
    });

    if ($("Plugins Plugin_MDStatus Unused_Devices", xml).length > 0) {
        $("#Plugin_MDStatusTable").append("<tr><td>" + genlang(15, false, "MDStatus") + "</td><td>" + $(this).attr("Devices") + "</td></tr>");
        mdstatus_show = true;
    }
}

/**
 * load the xml via ajax
 */
function mdstatus_request() {
    $.ajax({
        url: "xml.php?plugin=MDStatus",
        dataType: "xml",
        error: function mdstatus_error() {
            $.jGrowl("Error loading XML document for Plugin MDStatus");
        },
        success: function mdstatus_buildblock(xml) {
            populateErrors(xml);
            mdstatus_populate(xml);
            if (mdstatus_show) {
                plugin_translate("MDStatus");
                $("#Plugin_MDStatus").show();
            }
        }
    });
}

$(document).ready(function mdstatus_buildpage() {
    var html = "";

    $("#footer").before(buildBlock("MDStatus", 1, true));
    html += "        <table id=\"Plugin_MDStatusTable\" style=\"border-spacing:0;\">\n";
    html += "        </table>\n";
    $("#Plugin_MDStatus").append(html);

    $("#Plugin_MDStatus").css("width", "915px");

    mdstatus_request();

    $("#Reload_MDStatusTable").click(function mdstatus_reload(id) {
        mdstatus_request();
        $("#Reload_MDStatusTable").attr("title",datetime());
    });
});
