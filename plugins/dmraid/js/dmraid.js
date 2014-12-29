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
// $Id: dmraid.js 679 2012-09-04 10:10:11Z namiltd $
//

/*global $, jQuery, buildBlock, genlang, createBar, plugin_translate, datetime */

"use strict";

var dmraid_show = false;

/**
 * get the details of the raid
 * @param {jQuery} xml part of the plugin-XML
 * @param {number} id id of the device
 */
function dmraid_buildinfos(xml, id) {
    var html = "", devname = "", devstatus = "", devtype = "",devsize = 0, devstride = 0, devsubsets = 0, devdevs = 0, devspares = 0, button = "";

    devname = $(xml).attr("Name");
    devstatus = $(xml).attr("Disk_Status");
    devtype = $(xml).attr("Type");
    devsize = parseInt($(xml).attr("Size"), 10);
    devstride = parseInt($(xml).attr("Stride"), 10);
    devsubsets = parseInt($(xml).attr("Subsets"), 10);
    devdevs = parseInt($(xml).attr("Devs"), 10);
    devspares = parseInt($(xml).attr("Spares"), 10);
    html += "<tr><td>" + genlang(4, false, "DMRaid") + "</td><td>" + devname + "</td></tr>";
    html += "<tr><td>" + genlang(5, false, "DMRaid") + "</td><td>" + devstatus + "</td></tr>";
    html += "<tr><td>" + genlang(6, false, "DMRaid") + "</td><td>" + devtype + "</td></tr>";
    html += "<tr><td>" + genlang(7, false, "DMRaid") + "</td><td>" + devsize + "</td></tr>";
    html += "<tr><td>" + genlang(8, false, "DMRaid") + "</td><td>" + devstride + "</td></tr>";
    html += "<tr><td>" + genlang(9, false, "DMRaid") + "</td><td>" + devsubsets + "</td></tr>";
    html += "<tr><td>" + genlang(10, false, "DMRaid") + "</td><td>" + devdevs + "</td></tr>";
    html += "<tr><td>" + genlang(11, false, "DMRaid") + "</td><td>" + devspares + "</td></tr>";
    button += "<h3 style=\"cursor: pointer\" id=\"sPlugin_DMRaid_Info" + id + "\"><img src=\"./gfx/bullet_toggle_plus.png\" alt=\"plus\" style=\"vertical-align:middle;\" />" + genlang(3, false, "DMRaid") + "</h3>";
    button += "<h3 style=\"cursor: pointer; display: none;\" id=\"hPlugin_DMRaid_Info" + id + "\"><img src=\"./gfx/bullet_toggle_minus.png\" alt=\"minus\" style=\"vertical-align:middle;\" />" + genlang(3, false, "DMRaid") + "</h3>";
    button += "<table id=\"Plugin_DMRaid_InfoTable" + id + "\" style=\"border-spacing:0; display:none;\">" + html + "</table>";
    return button;
}

/**
 * choose the right diskdrive icon
 * @param {jQuery} xml part of the plugin-XML
 */
function dmraid_diskicon(xml) {
    var html = "";
    $("Disks Disk", xml).each(function dmraid_getdisk(id) {
        var diskstatus = "", diskname = "", img = "", alt = "";
        html += "<div class=\"plugin_dmraid_biun\">";
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
        case "W":
            img = "harddrivewarn.png";
            alt = "fail";
            break;
        default:
            alert("--" + diskstatus + "--");
            img = "error.png";
            alt = "error";
            break;
        }
        html += "<img class=\"plugin_dmraid_biun\" src=\"./plugins/dmraid/gfx/" + img + "\" alt=\"" + alt + "\" />";
        html += "<small>" + diskname + "</small>";
        html += "</div>";
    });
    return html;
}

/**
 * fill the plugin block
 * @param {jQuery} xml plugin-XML
 */
function dmraid_populate(xml) {
    var htmltypes = "";

    $("#Plugin_DMRaidTable").empty();
    $("Plugins Plugin_DMRaid Raid", xml).each(function dmraid_getdevice(id) {
        var htmldisks = "", htmldisklist = "", topic = "", name = "", buildedaction = "";
        name = $(this).attr("Device_Name");
        htmldisklist += dmraid_diskicon(this);
        htmldisks += "<table style=\"width:100%;\">";
        htmldisks += "<tr><td>" + htmldisklist + "</td></tr>";
        htmldisks += "<tr><td>" + dmraid_buildinfos($(this), id) + "<td></tr>";
        htmldisks += "</table>";
        if (id) {
            topic = "";
        }
        else {
            topic = genlang(2, false, "DMRaid");
        }
        $("#Plugin_DMRaidTable").append("<tr><td>" + topic + "</td><td><div class=\"plugin_dmraid_biun\" style=\"text-align:left;\"><b>" + name + "</b></div>" + htmldisks + "</td></tr>");
        $("#sPlugin_DMRaid_Info" + id).click(function dmraid_showinfo() {
            $("#Plugin_DMRaid_InfoTable" + id).slideDown("slow");
            $("#sPlugin_DMRaid_Info" + id).hide();
            $("#hPlugin_DMRaid_Info" + id).show();
        });
        $("#hPlugin_DMRaid_Info" + id).click(function dmraid_hideinfo() {
            $("#Plugin_DMRaid_InfoTable" + id).slideUp("slow");
            $("#hPlugin_DMRaid_Info" + id).hide();
            $("#sPlugin_DMRaid_Info" + id).show();
        });
        dmraid_show = true;
    });
}

/**
 * load the xml via ajax
 */
function dmraid_request() {
    $.ajax({
        url: "xml.php?plugin=DMRaid",
        dataType: "xml",
        error: function dmraid_error() {
            $.jGrowl("Error loading XML document for Plugin DMRaid");
        },
        success: function dmraid_buildblock(xml) {
            populateErrors(xml);
            dmraid_populate(xml);
            if (dmraid_show) {
                plugin_translate("DMRaid");
                $("#Plugin_DMRaid").show();
            }
        }
    });
}

$(document).ready(function dmraid_buildpage() {
    var html = "";

    $("#footer").before(buildBlock("DMRaid", 1, true));
    html += "        <table id=\"Plugin_DMRaidTable\" style=\"border-spacing:0;\">\n";
    html += "        </table>\n";
    $("#Plugin_DMRaid").append(html);

    $("#Plugin_DMRaid").css("width", "915px");

    dmraid_request();

    $("#Reload_DMRaidTable").click(function dmraid_reload(id) {
        dmraid_request();
        $("#Reload_DMRaidTable").attr("title",datetime());
    });
});
