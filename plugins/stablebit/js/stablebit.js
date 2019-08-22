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
// $Id: stablebit.js 661 2012-08-27 11:26:39Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang */

"use strict";

var stablebit_show = false;

/**
 * build the table where content is inserted
 * @param {jQuery} xml plugin-XML
 */
function stablebit_buildTable(xml) {
    var html = "", tree = [], closed = [];

    $("#Plugin_StableBit #Plugin_StableBitTable").remove();

    html += "  <div style=\"overflow-x:auto;\">\n";
    html += "    <table id=\"Plugin_StableBitTable\" class=\"tablemain\">\n";
    html += "     <thead>\n";
    html += "      <tr>\n";
    html += "       <th>" + genlang(2, "StableBit") + "</th>\n";
    html += "       <th style=\"width:120px;\">" + genlang(3, "StableBit") + "</th>\n";
    html += "      </tr>\n";
    html += "     </thead>\n";
    html += "     <tbody class=\"tree\">\n";

    var index = 0;

    $("Plugins Plugin_StableBit Disk", xml).each(function stablebit_getdisks(id) {
        var name = "";
        name = $(this).attr("Name");
        if (name !== undefined) {
            var serialnumber = "", firmware = "", size = 0, powerstate = "", temperaturec = "",
            ishot = 0, issmartwarning = 0, issmartpastthresholds = 0, issmartpastadvisorythresholds = 0, 
            issmartfailurepredicted = 0, isdamaged = 0;

            html += "      <tr><td colspan=\"2\"><div class=\"treediv\"><span class=\"treespanbold\">" + name + "</div></span></td></tr>\n";
            index = tree.push(0);

            serialnumber = $(this).attr("SerialNumber");
            if (serialnumber !== undefined) {
                html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(4, "StableBit") + "</div></span></td><td>" + serialnumber +"</td></tr>\n";
                tree.push(index);
            }
            firmware = $(this).attr("Firmware");
            if (firmware !== undefined) {
                html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(5, "StableBit") + "</div></span></td><td>" + firmware +"</td></tr>\n";
                tree.push(index);
            }
            size = parseInt($(this).attr("Size"), 10);
            if (!isNaN(size)) {
                html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(6, "StableBit") + "</div></span></td><td>" + formatBytes(size, xml) +"</td></tr>\n";
                tree.push(index);
            }        
            powerstate = $(this).attr("PowerState");
            if (powerstate !== undefined) {
                html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(7, "StableBit") + "</div></span></td><td>" + powerstate +"</td></tr>\n";
                tree.push(index);
            }
            temperaturec = $(this).attr("TemperatureC");
            if (temperaturec !== undefined) {
                html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(8, "StableBit") + "</div></span></td><td>" + formatTemp(temperaturec, xml) +"</td></tr>\n";
                tree.push(index);
            }
            if ($(this).attr("IsHot") !== undefined) {
                ishot = parseInt($(this).attr("IsHot"), 10);
                if (!isNaN(ishot) && (ishot === 1)) {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(9, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/on.gif\" alt=\"on\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                else {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(9, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/off.gif\" alt=\"off\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                tree.push(index);
            }
            if ($(this).attr("IsSmartWarning") !== undefined) {
                issmartwarning = parseInt($(this).attr("IsSmartWarning"), 10);
                if (!isNaN(issmartwarning) && (issmartwarning === 1)) {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(10, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/on.gif\" alt=\"on\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                else {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(10, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/off.gif\" alt=\"off\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                tree.push(index);
            }
            if ($(this).attr("IsSmartPastThresholds") !== undefined) {
                issmartpastthresholds = parseInt($(this).attr("IsSmartPastThresholds"), 10);
                if (!isNaN(issmartpastthresholds) && (issmartpastthresholds === 1)) {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(11, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/on.gif\" alt=\"on\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                else {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(11, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/off.gif\" alt=\"off\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                tree.push(index);
            }
            if ($(this).attr("IsSmartPastAdvisoryThresholds") !== undefined) {
                issmartpastadvisorythresholds = parseInt($(this).attr("IsSmartPastAdvisoryThresholds"), 10);
                if (!isNaN(issmartpastadvisorythresholds) && (issmartpastadvisorythresholds === 1)) {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(12, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/on.gif\" alt=\"on\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                else {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(12, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/off.gif\" alt=\"off\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                tree.push(index);
            }
            if ($(this).attr("IsSmartFailurePredicted") !== undefined) {
                issmartfailurepredicted = parseInt($(this).attr("IsSmartFailurePredicted"), 10);
                if (!isNaN(issmartfailurepredicted) && (issmartfailurepredicted === 1)) {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(13, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/on.gif\" alt=\"on\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                else {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(13, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/off.gif\" alt=\"off\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                tree.push(index);
            }
            if ($(this).attr("IsDamaged") !== undefined) {
                isdamaged = parseInt($(this).attr("IsDamaged"), 10);
                if (!isNaN(isdamaged) && (isdamaged === 1)) {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(14, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/on.gif\" alt=\"on\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                else {
                    html += "      <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(14, "StableBit") + "</div></span></td><td><img src=\"./plugins/stablebit/gfx/off.gif\" alt=\"off\" title=\"\" style=\"width:18px;\" /></td></tr>\n";
                }
                tree.push(index);
            }

            stablebit_show = true;
        }
    });

    html += "     </tbody>\n";
    html += "    </table>\n";
    html += "  </div>\n";

    $("#Plugin_StableBit").append(html);

    $("#Plugin_StableBitTable").jqTreeTable(tree, {
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
 * load the xml via ajax
 */
function stablebit_request() {
    $("#Reload_StableBitTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=StableBit",
        dataType: "xml",
        error: function stablebit_error() {
            $.jGrowl("Error loading XML document for Plugin StableBit!");
        },
        success: function stablebit_buildblock(xml) {
            populateErrors(xml);
            stablebit_buildTable(xml);
            if (stablebit_show) {
                plugin_translate("StableBit");
                $("#Plugin_StableBit").show();
            }
        }
    });
}

$(document).ready(function stablebit_buildpage() {
    $("#footer").before(buildBlock("StableBit", 1, true));
    $("#Plugin_StableBit").css("width", "451px");

    stablebit_request();

    $("#Reload_StableBitTable").click(function stablebit_reload(id) {
        stablebit_request();
        $(this).attr("title", datetime());
    });
});
