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
// $Id: bat.js 661 2012-08-27 11:26:39Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang */

"use strict";

var bat_show = false;

/**
 * build the table where content is inserted
 * @param {jQuery} xml plugin-XML
 */
function bat_buildTable(xml) {
    var html = "", tree = [], closed = [], batcount = 0, index = 0, hostname = "";

    $("#Plugin_BAT #Plugin_BATTable").remove();

    hostname = $("Plugins Plugin_BAT", xml).attr('Hostname');
    if (hostname !== undefined) {
        $('span[class=Hostname_BAT]').html(hostname);
    }

    html += "  <div style=\"overflow-x:auto;\">\n";
    html += "   <table id=\"Plugin_BATTable\" class=\"tablemain\">\n";
    html += "    <thead>\n";
    html += "     <tr>\n";
    html += "      <th>" + genlang(6, "BAT") + "</th>\n";
    html += "      <th style=\"width:31%;\">" + genlang(7, "BAT") + "</th>\n";
    html += "      <th></th>\n";
    html += "     </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody class=\"tree\">\n";

    $("Plugins Plugin_Bat Bat", xml).each(function bat_getbats(id) {
        var name = "", DesignCapacity = 0, FullCapacity = 0, DesignVoltage = "",  BatteryType = "", RemainingCapacity = 0, PresentVoltage = "", ChargingState = "", BatteryTemperature = "", BatteryCondition = "", CapacityUnit = "", CycleCount = "", DesignVoltageMax = "", Manufacturer = "", Model = "", SerialNumber = "";
        name = $(this).attr("Name");
        if (name === undefined) {
            name = "Battery"+(batcount++);
        }
        DesignCapacity = parseInt($(this).attr("DesignCapacity"), 10);
        FullCapacity = parseInt($(this).attr("FullCapacity"), 10);
        DesignVoltage = $(this).attr("DesignVoltage");
        BatteryType = $(this).attr("BatteryType");
        RemainingCapacity = parseInt($(this).attr("RemainingCapacity"), 10);
        PresentVoltage = $(this).attr("PresentVoltage");
        ChargingState = $(this).attr("ChargingState");
        BatteryTemperature = $(this).attr("BatteryTemperature");
        BatteryCondition = $(this).attr("BatteryCondition");
        CapacityUnit = $(this).attr("CapacityUnit");
        CycleCount = $(this).attr("CycleCount");
        DesignVoltageMax = $(this).attr("DesignVoltageMax");
        Manufacturer = $(this).attr("Manufacturer");
        Model = $(this).attr("Model");
        SerialNumber = $(this).attr("SerialNumber");

        html += "     <tr><td colspan=\"3\"><div class=\"treediv\"><span class=\"treespanbold\">" + name + "</div></span></td></tr>\n";
        index = tree.push(0);

        if (Model !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(15, "BAT") + "</div></span></td><td>" + Model +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (Manufacturer !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(14, "BAT") + "</div></span></td><td>" + Manufacturer +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (SerialNumber !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(16, "BAT") + "</div></span></td><td>" + SerialNumber +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (CapacityUnit === undefined) {
            CapacityUnit = "mWh";
        }
        if ((CapacityUnit == "%") && ($(this).attr("RemainingCapacity") !== undefined)) {
           if (!isNaN(RemainingCapacity)) {
                html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(3, "BAT") + "</div></span></td><td>" + createBar(RemainingCapacity) +"</td><td></td></tr>\n";
                tree.push(index);
            }
        } else {
            if (!isNaN(DesignCapacity)) {
                html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(2, "BAT") + "</div></span></td><td>" + DesignCapacity+' '+CapacityUnit +"</td><td></td></tr>\n";
                tree.push(index);
            }
            if (isNaN(FullCapacity)) {
                if (!isNaN(RemainingCapacity)) {
                    html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(3, "BAT") + "</div></span></td><td>" + RemainingCapacity+' '+CapacityUnit +"</td><td></td></tr>\n";
                    tree.push(index);
                }
            } else {
                if (isNaN(DesignCapacity)) {
                    html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(13, "BAT") + "</div></span></td><td>" + FullCapacity+' '+CapacityUnit +"</td><td></td></tr>\n";
                    tree.push(index);
                } else {
                    html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(13, "BAT") + "</div></span></td><td>" + FullCapacity+' '+CapacityUnit +"</td><td>" + createBar(DesignCapacity !== 0 ? round(FullCapacity / DesignCapacity * 100, 0) : 0) + "</td></tr>\n";
                    tree.push(index);
                }
                if (!isNaN(RemainingCapacity)) {
                    html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(3, "BAT") + "</div></span></td><td>" + RemainingCapacity+' '+CapacityUnit +"</td><td>" + createBar(FullCapacity !== 0 ? round(RemainingCapacity / FullCapacity * 100, 0) : 0) + "</td></tr>\n";
                    tree.push(index);
                }
            }
        }
        if (ChargingState !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(8, "BAT") + "</div></span></td><td>" + ChargingState +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (DesignVoltage !== undefined) {
            if (DesignVoltageMax !== undefined) {
                html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(4, "BAT") + "</div></span></td><td>" + DesignVoltage+' mV' +"</td><td>" + DesignVoltageMax+' mV'+ "</td></tr>\n";
                tree.push(index);
            } else {
                html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(4, "BAT") + "</div></span></td><td>" + DesignVoltage+' mV' +"</td><td></td></tr>\n";
                tree.push(index);
            }
        } else if (DesignVoltageMax !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(4, "BAT") + "</div></span></td><td>" + DesignVoltageMax+' mV' +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (PresentVoltage !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(5, "BAT") + "</div></span></td><td>" + PresentVoltage+' mV' +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (BatteryType !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(9, "BAT") + "</div></span></td><td>" + BatteryType +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (BatteryTemperature !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(10, "BAT") + "</div></span></td><td>" + formatTemp(BatteryTemperature, xml) +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (BatteryCondition !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(11, "BAT") + "</div></span></td><td>" + BatteryCondition +"</td><td></td></tr>\n";
            tree.push(index);
        }
        if (CycleCount !== undefined) {
            html += "     <tr><td><div class=\"treediv\"><span class=\"treespan\">" + genlang(12, "BAT") + "</div></span></td><td>" + CycleCount +"</td><td></td></tr>\n";
            tree.push(index);
        }

        bat_show = true;
    });

    html += "    </tbody>\n";
    html += "   </table>\n";
    html += "  </div>\n";

    $("#Plugin_BAT").append(html);

    $("#Plugin_BATTable").jqTreeTable(tree, {
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
function bat_request() {
    $("#Reload_BATTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=BAT",
        dataType: "xml",
        error: function bat_error() {
            $.jGrowl("Error loading XML document for Plugin BAT!");
        },
        success: function bat_buildblock(xml) {
            populateErrors(xml);
            bat_buildTable(xml);
            if (bat_show) {
                plugin_translate("BAT");
                $("#Plugin_BAT").show();
            }
        }
    });
}

$(document).ready(function bat_buildpage() {
    $("#footer").before(buildBlock("BAT", 1, true));
    $("#Plugin_BAT").addClass("halfsize");

    bat_request();

    $("#Reload_BATTable").click(function bat_reload(id) {
        bat_request();
        $(this).attr("title", datetime());
    });
});
