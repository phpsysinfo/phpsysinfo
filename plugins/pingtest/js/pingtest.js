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
// $Id: pingtest.js 1 2017-09-01 08:23:45Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang */

"use strict";

var pingtest_show = false, pingtest_table;


/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function pingtest_populate(xml) {
    var address = "", pingtime = 0, state = "";

    pingtest_table.fnClearTable();

    $("Plugins Plugin_PingTest Ping", xml).each(function pingtest_getprocess(idp) {
        address = $(this).attr("Address");
        pingtime = parseInt($(this).attr("PingTime"), 10);
        if (!isNaN(pingtime)) {
            state = "<span style=\"display:none;\">" + pingtime.toString() + "</span>" + pingtime.toString() + "&nbsp;ms";
        }
        else {
            state = "<span style=\"display:none;\">1000000</span>" + genlang(4, "PingTest");
        }
        pingtest_table.fnAddData(["<span style=\"display:none;\">" + address + "</span>" + address, state]);
        pingtest_show = true;
    });
}

/**
 * fill the plugin block with table structure
 */
function pingtest_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_PingTestTable\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(2, "PingTest") + "</th>\n";
    html += "        <th>" + genlang(3, "PingTest") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody>\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";

    $("#Plugin_PingTest").append(html);

    pingtest_table = $("#Plugin_PingTestTable").dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": true,
        "bInfo": false,
        "bProcessing": true,
        "bAutoWidth": false,
        "bStateSave": true,
        "aoColumns": [{
            "sType": 'span-ip'
        }, {
            "sType": 'span-number'
        }]
    });
}

/**
 * load the xml via ajax
 */
function pingtest_request() {
    $("#Reload_PingTestTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=PingTest",
        dataType: "xml",
        error: function pingtest_error() {
            $.jGrowl("Error loading XML document for Plugin PingTest!");
        },
        success: function pingtest_buildblock(xml) {
            populateErrors(xml);
            pingtest_populate(xml);
            if (pingtest_show) {
                plugin_translate("PingTest");
                $("#Plugin_PingTest").show();
            }
        }
    });
}

$(document).ready(function pingtest_buildpage() {
    $("#footer").before(buildBlock("PingTest", 1, true));
    $("#Plugin_PingTest").css("width", "451px");

    pingtest_buildTable();

    pingtest_request();

    $("#Reload_PingTestTable").click(function pingtest_reload(id) {
        pingtest_request();
        $(this).attr("title", datetime());
    });
});
