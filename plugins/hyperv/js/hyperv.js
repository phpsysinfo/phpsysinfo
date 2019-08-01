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
// $Id: hyperv.js 679 2012-09-04 10:10:11Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang */

"use strict";

var hyperv_show = false, hyperv_table;

//appendcss("./plugins/hyperv/css/hyperv.css");

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function hyperv_populate(xml) {
    var name = "", status = 0, state = "";

    hyperv_table.fnClearTable();

    $("Plugins Plugin_HyperV Machine", xml).each(function hyperv_getprocess(idp) {
        name = $(this).attr("Name");
        status = parseInt($(this).attr("State"), 10);
        if (!isNaN(status) && (status === 2)) {
            state = "<span style=\"display:none;\">" + status.toString() + "</span><img src=\"./plugins/hyperv/gfx/online.gif\" alt=\"online\" title=\"\" style=\"width:18px;\" />";
        }
        else {
            state = "<span style=\"display:none;\">" + status.toString() + "</span><img src=\"./plugins/hyperv/gfx/offline.gif\" alt=\"offline\" title=\"\" style=\"width:18px;\" />";
        }
        hyperv_table.fnAddData(["<span style=\"display:none;\">" + name + "</span>" + name, state]);
        hyperv_show = true;
    });
}

/**
 * fill the plugin block with table structure
 */
function hyperv_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_HyperVTable\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(2, "HyperV") + "</th>\n";
    html += "        <th>" + genlang(3, "HyperV") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody>\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";

    $("#Plugin_HyperV").append(html);

    hyperv_table = $("#Plugin_HyperVTable").dataTable({
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
function hyperv_request() {
    $("#Reload_HyperVTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=HyperV",
        dataType: "xml",
        error: function hyperv_error() {
            $.jGrowl("Error loading XML document for Plugin HyperV!");
        },
        success: function hyperv_buildblock(xml) {
            populateErrors(xml);
            hyperv_populate(xml);
            if (hyperv_show) {
                plugin_translate("HyperV");
                $("#Plugin_HyperV").show();
            }
        }
    });
}

$(document).ready(function hyperv_buildpage() {
    $("#footer").before(buildBlock("HyperV", 1, true));
    $("#Plugin_HyperV").css("width", "451px");

    hyperv_buildTable();

    hyperv_request();

    $("#Reload_HyperVTable").click(function hyperv_reload(id) {
        hyperv_request();
        $(this).attr("title", datetime());
    });
});
