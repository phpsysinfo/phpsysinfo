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

//$Id: smart.js 707 2012-11-28 10:20:49Z namiltd $


/*global $, jQuery, genlang, formatTemp, plugin_translate, buildBlock, datetime */

"use strict";

var smart_show = false, smart_table;

//appendcss("./plugins/SMART/css/SMART.css");

/**
 * fill the plugin block with table structure
 */
function smart_buildTable(xml) {
    var html = "", hostname = "";
    var smartid;
    var attribute_name;

    hostname = $("Plugins Plugin_SMART", xml).attr('Hostname');
    if (hostname !== undefined) {
        $('span[class=Hostname_SMART]').html(hostname);
    }

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_SMARTTable\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th class=\"right\">" + genlang(2, "SMART") + "</th>\n";
    $("Plugins Plugin_SMART columns column", xml).each(function smart_table_header() {
        attribute_name = $(this).attr("attribute_name");
        if (typeof attribute_name === 'string')
            attribute_name = attribute_name.replace(/_/g, " ").replace(/;/g, "<br>");
        else
            attribute_name = "Attribute " + $(this).attr("id");

        html += "        <th class=\"right\">" + attribute_name + "</th>\n";
    });
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody>\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";

    $("#Plugin_SMART").append(html);

    smart_table = $("#Plugin_SMARTTable").dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": true,
        "bInfo": false,
        "bProcessing": true,
        "bAutoWidth": false,
        "bStateSave": true
    });
}

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function smart_populate(xml) {
    var name = "", event = "", columns = [];
    smart_table.fnClearTable();

    // Get datas that the user want to be displayed
    $("Plugins Plugin_SMART columns column", xml).each(function smart_find_columns() {
        columns[parseInt($(this).attr("id"), 10)] = $(this).attr("name");
        smart_show = true;
    });

    // Now we add selected datas in the table
    $("Plugins Plugin_SMART disks disk", xml).each(function smart_fill_table() {
        var values = [], display = [], i;
        name = $(this).attr("name").replace(/\)/g, ")<wbr>");
        $(this).find("attribute").each(function smart_fill_data() {
            if (columns[parseInt($(this).attr("id"), 10)] && columns[parseInt($(this).attr("id"), 10)] !== "") {
                values[parseInt($(this).attr("id"), 10)] = $(this).attr(columns[parseInt($(this).attr("id"), 10)]);
            }
        });

        event = $(this).attr("event");
        if (event !== undefined)
            display.push("<span style=\"display:none;\">" + name + "</span><table style=\"width:0;float:right;*float:right;border-spacing:0;\"><tbody><tr><td style=\"padding:0;\">" + name +" </td><td style=\"padding:0;\"><img style=\"vertical-align:middle;width:16px;\" src=\"./gfx/attention.gif\" alt=\"!\" title=\""+event+"\"/></td></tr></tbody></table>");
        else
            display.push("<span style=\"display:none;\">" + name + "</span>" + name);

        // On "columns" so we get the right order
        // fixed for Firefox (fix wrong order)
        $("Plugins Plugin_SMART columns column", xml).each(function smart_find_columns() {
            i  = parseInt($(this).attr("id"), 10);
            if (typeof(values[i])==='undefined') {
                display.push("<span style=\"display:none;\"></span>");
            }
            else if (i === 194) {
                display.push("<span style=\"display:none;\">" + values[i] + "</span>" + formatTemp(values[i], xml));
            }
            else {
                display.push("<span style=\"display:none;\">" + values[i] + "</span>" + values[i]);
            }
        });
        smart_table.fnAddData(display);
    });
}

/**
 * load the xml via ajax
 */
function smart_request() {
    $("#Reload_SMARTTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=SMART",
        dataType: "xml",
        error: function smart_error() {
            $.jGrowl("Error loading XML document for Plugin SMART");
        },
        success: function smart_buildBlock(xml) {
            populateErrors(xml);
            if ((smart_table === undefined) || (typeof(smart_table) !== "object")) {
                smart_buildTable(xml);
            }
            smart_populate(xml);
            if (smart_show) {
                plugin_translate("SMART");
                $("#Plugin_SMART").show();
            }
        }
    });
}

$(document).ready(function smart_buildpage() {
    $("#footer").before(buildBlock("SMART", 1, true));
    $("#Plugin_SMART").addClass("fullsize");

    smart_request();

    $("#Reload_SMARTTable").click(function smart_reload(id) {
        smart_request();
        $(this).attr("title", datetime());
    });
});
