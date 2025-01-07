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

//$Id: uprecords.js 661 2014-01-08 11:26:39 aolah76 $


/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang */

"use strict";

var uprecords_show = false;

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */

function uprecords_populate(xml) {
    var html = "", datetimeFormat = "", hostname = "";

    hostname = $("Plugins Plugin_uprecords", xml).attr('Hostname');
    if (hostname !== undefined) {
        $('span[class=Hostname_uprecords]').html(hostname);
    }

    $("Options", xml).each(function getByteFormat(id) {
        datetimeFormat = $(this).attr("datetimeFormat");
    });

    $("Plugins Plugin_uprecords uprecords Item", xml).each(function uprecords_getitem(idp) {
        html += "      <tr>\n";
        html += "        <td style=\"font-weight:normal\">" +  $(this).attr("hash") + "</td>\n";
        html += "        <td style=\"font-weight:normal\">" +  $(this).attr("Uptime") + "</td>\n";
        html += "        <td style=\"font-weight:normal\">" +  $(this).attr("System") + "</td>\n";
/*
        var lastboot = new Date($(this).attr("Bootup"));
        if (typeof(lastboot.toUTCString)==="function") {
            html += "        <td style=\"font-weight:normal\">" +  lastboot.toUTCString() + "</td>\n";
        } else { //deprecated
            html += "        <td style=\"font-weight:normal\">" +  lastboot.toGMTString() + "</td>\n";
        }
*/
        if ((datetimeFormat !== undefined) && (datetimeFormat.toLowerCase() === "locale")) {
            var lastboot = new Date($(this).attr("Bootup"));
            html += "        <td style=\"font-weight:normal\">" +  lastboot.toLocaleString() + "</td>\n";
        } else {
            html += "        <td style=\"font-weight:normal\">" +  $(this).attr("Bootup") + "</td>\n";
        }
        html += "      </tr>\n";
        uprecords_show = true;
    });

    $("#Plugin_uprecordsTable-tbody").empty().append(html);
    $('#Plugin_uprecordsTable tr:nth-child(even)').addClass('even');

}

function uprecords_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_uprecordsTable\" class=\"stripeMe\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(101, "uprecords") + "</th>\n";
    html += "        <th>" + genlang(102, "uprecords") + "</th>\n";
    html += "        <th>" + genlang(103, "uprecords") + "</th>\n";
    html += "        <th>" + genlang(104, "uprecords") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody id=\"Plugin_uprecordsTable-tbody\">\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";
    $("#Plugin_uprecords").append(html);
}

/**
 * load the xml via ajax
 */

function uprecords_request() {
    $("#Reload_uprecordsTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=uprecords",
        dataType: "xml",
        error: function uprecords_error() {
            $.jGrowl("Error loading XML document for Plugin uprecords!");
        },
        success: function uprecords_buildblock(xml) {
            populateErrors(xml);
            uprecords_populate(xml);
            if (uprecords_show) {
                plugin_translate("uprecords");
                $("#Plugin_uprecords").show();
            }
        }
    });
}

$(document).ready(function uprecords_buildpage() {
    $("#footer").before(buildBlock("uprecords", 1, true));
    $("#Plugin_uprecords").addClass("fullsize");

    uprecords_buildTable();

    uprecords_request();

    $("#Reload_uprecordsTable").click(function uprecords_reload(id) {
        uprecords_request();
        $(this).attr("title", datetime());
    });
});
