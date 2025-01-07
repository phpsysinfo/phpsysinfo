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
// $Id: psstatus.js 679 2012-09-04 10:10:11Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang */

"use strict";

var psstatus_show = false, psstatus_table;

//appendcss("./plugins/PSStatus/css/PSStatus.css");

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function psstatus_populate(xml) {
    var name = "", status = 0, state = "", hostname = "";

    psstatus_table.fnClearTable();

    hostname = $("Plugins Plugin_PSStatus", xml).attr('Hostname');
    if (hostname !== undefined) {
        $('span[class=Hostname_PSStatus]').html(hostname);
    }

    $("Plugins Plugin_PSStatus Process", xml).each(function psstatus_getprocess(idp) {
        name = $(this).attr("Name");
        status = parseInt($(this).attr("Status"), 10);
        if (!isNaN(status) && (status === 1)) {
            state = "<span style=\"display:none;\">" + status.toString() + "</span><img src=\"./plugins/psstatus/gfx/online.gif\" alt=\"online\" title=\"\" style=\"width:18px;\" />";
        }
        else {
            state = "<span style=\"display:none;\">" + status.toString() + "</span><img src=\"./plugins/psstatus/gfx/offline.gif\" alt=\"offline\" title=\"\" style=\"width:18px;\" />";
        }
        psstatus_table.fnAddData(["<span style=\"display:none;\">" + name + "</span>" + name, state]);
        psstatus_show = true;
    });
}

/**
 * fill the plugin block with table structure
 */
function psstatus_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_PSStatusTable\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(2, "PSStatus") + "</th>\n";
    html += "        <th>" + genlang(3, "PSStatus") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody>\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";

    $("#Plugin_PSStatus").append(html);

    psstatus_table = $("#Plugin_PSStatusTable").dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": true,
        "bInfo": false,
        "bProcessing": true,
        "bAutoWidth": false,
        "bStateSave": true,
        "aoColumns": [{
            "sType": 'span-string'
        }, {
            "sType": 'span-number'
        }]
    });
}

/**
 * load the xml via ajax
 */
function psstatus_request() {
    $("#Reload_PSStatusTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=PSStatus",
        dataType: "xml",
        error: function psstatus_error() {
            $.jGrowl("Error loading XML document for Plugin PSStatus!");
        },
        success: function psstatus_buildblock(xml) {
            populateErrors(xml);
            psstatus_populate(xml);
            if (psstatus_show) {
                plugin_translate("PSStatus");
                $("#Plugin_PSStatus").show();
            }
        }
    });
}

$(document).ready(function psstatus_buildpage() {
    $("#footer").before(buildBlock("PSStatus", 1, true));
    $("#Plugin_PSStatus").addClass("halfsize");

    psstatus_buildTable();

    psstatus_request();

    $("#Reload_PSStatusTable").click(function psstatus_reload(id) {
        psstatus_request();
        $(this).attr("title", datetime());
    });
});
