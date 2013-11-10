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

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang, createBar */

"use strict";

var bat_show = false, bat_table;
/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function bat_populate(xml) {

    bat_table.fnClearTable();

    $("Plugins Plugin_BAT Bat", xml).each(function bat_getitem(idp) {
        var DesignCapacity = "", DesignVoltage = "",  BatteryType = "",RemainingCapacity = "", PresentVoltage = "", ChargingState = "", BatteryTemperature = "", BatteryCondition = "", Capacity = "";
        DesignCapacity = $(this).attr("DesignCapacity");
        DesignVoltage = $(this).attr("DesignVoltage");
        BatteryType = $(this).attr("BatteryType");
        RemainingCapacity = $(this).attr("RemainingCapacity");
        PresentVoltage = $(this).attr("PresentVoltage");
        ChargingState = $(this).attr("ChargingState");
        BatteryTemperature = $(this).attr("BatteryTemperature");
        BatteryCondition = $(this).attr("BatteryCondition");
        Capacity = $(this).attr("Capacity");

        if (Capacity != undefined) {
            bat_table.fnAddData([genlang(4, true, "BAT"), createBar(parseInt(Capacity, 10)), '&nbsp;']);
        } else if (DesignCapacity == undefined) {
            if (RemainingCapacity != undefined) bat_table.fnAddData([genlang(4, true, "BAT"), RemainingCapacity+' mWh', '&nbsp;']);
        } else {
            bat_table.fnAddData([genlang(3, true, "BAT"), DesignCapacity+' mWh', '&nbsp;']);
            if (RemainingCapacity != undefined) bat_table.fnAddData([genlang(4, true, "BAT"), RemainingCapacity+' mWh', createBar(parseInt(parseInt(RemainingCapacity, 10) / parseInt(DesignCapacity, 10) * 100, 10))]);
        }
        if (ChargingState != undefined) {
            bat_table.fnAddData([genlang(9, true, "BAT"), ChargingState, '&nbsp;']);
        }
        if (DesignVoltage != undefined) {
            bat_table.fnAddData([genlang(5, true, "BAT"), DesignVoltage+' mV', '&nbsp;']);
        }
        if (PresentVoltage != undefined) {
            bat_table.fnAddData([genlang(6, true, "BAT"), PresentVoltage+' mV', '&nbsp;']);
        }
        if (BatteryType != undefined) {
            bat_table.fnAddData([genlang(10, true, "BAT"), BatteryType, '&nbsp;']);
        }
        if (BatteryTemperature != undefined) {
            bat_table.fnAddData([genlang(11, true, "BAT"), formatTemp(BatteryTemperature, xml), '&nbsp;']);
        }
        if (BatteryCondition != undefined) {
            bat_table.fnAddData([genlang(12, true, "BAT"), BatteryCondition, '&nbsp;']);
        }

        bat_show = true;
    });
}

/**
 * fill the plugin block with table structure
 */
function bat_buildTable() {
    var html = "";

    html += "<table id=\"Plugin_BATTable\" style=\"border-spacing:0;\">\n";
    html += "  <thead>\n";
    html += "    <tr>\n";
    html += "      <th>" + genlang(7, true, "BAT") + "</th>\n";
    html += "      <th>" + genlang(8, true, "BAT") + "</th>\n";
    html += "      <th>&nbsp;</th>\n";
    html += "    </tr>\n";
    html += "  </thead>\n";
    html += "  <tbody>\n";
    html += "  </tbody>\n";
    html += "</table>\n";

    $("#Plugin_BAT").append(html);

}

/**
 * load the xml via ajax
 */
function bat_request() {
    $.ajax({
        url: "xml.php?plugin=BAT",
        dataType: "xml",
        error: function bat_error() {
            $.jGrowl("Error loading XML document for Plugin BAT!");
        },
        success: function bat_buildblock(xml) {
            populateErrors(xml);
            bat_populate(xml);
            if (bat_show) {
                plugin_translate("BAT");
                $("#Plugin_BAT").show();
            }
        }
    });
}

$(document).ready(function bat_buildpage() {
    $("#footer").before(buildBlock("BAT", 1, true));
    $("#Plugin_BAT").css("width", "451px");

    bat_buildTable();

    bat_table = $("#Plugin_BATTable").dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": false,
        "bInfo": false,
        "bProcessing": true,
        "bAutoWidth": false,
        "bStateSave": true,
        "aoColumns": [{
            "sType": 'span-string'
        }, {
            "sType": 'span-string'
        }, {
            "sType": 'span-string'
        }]
    });

    bat_request();

    $("#Reload_BATTable").click(function bat_reload(id) {
        bat_request();
        $("#Reload_BATTable").attr("title",datetime());
    });
});
