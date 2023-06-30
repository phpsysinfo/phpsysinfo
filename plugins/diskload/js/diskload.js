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
// $Id: diskload.js 661 2012-08-27 11:26:39Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang */

"use strict";

var diskload_show = false;

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */

function diskload_populate(xml) {
    var html = "", datetimeFormat = "", hostname = "";

    hostname = $("Plugins Plugin_DiskLoad", xml).attr('Hostname');
    if (hostname !== undefined) {
        $('span[class=Hostname_diskload]').html(hostname);
    }

    $("Options", xml).each(function getByteFormat(id) {
        datetimeFormat = $(this).attr("datetimeFormat");
    });

    $("Plugins Plugin_DiskLoad Disk", xml).each(function diskload_getDisk(idp) {
        html += "      <tr>\n";
        html += "        <td style=\"font-weight:normal\">" +  $(this).attr("Name") + "</td>\n";
        html += "        <td style=\"font-weight:normal\">" +  createBar($(this).attr("Load")) + "</td>\n";
        html += "      </tr>\n";
        diskload_show = true;
    });

    $("#Plugin_DiskLoadTable-tbody").empty().append(html);
    $('#Plugin_DiskLoadTable tr:nth-child(even)').addClass('even');
}

/**
 * build the table where content is inserted
 * @param {jQuery} xml plugin-XML
 */
function diskload_buildTable(xml) {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_DiskLoadTable\" class=\"stripeMe\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(2, "diskload") + "</th>\n";
    html += "        <th style=\"width:37%;\">" + genlang(3, "diskload") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody id=\"Plugin_DiskLoadTable-tbody\">\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";
    $("#Plugin_DiskLoad").append(html);
}

/**
 * load the xml via ajax
 */
function diskload_request() {
    $("#Reload_DiskLoadTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=DiskLoad",
        dataType: "xml",
        error: function diskload_error() {
            $.jGrowl("Error loading XML document for Plugin DiskLoad!");
        },
        success: function diskload_buildblock(xml) {
            populateErrors(xml);
            diskload_populate(xml);
            if (diskload_show) {
                plugin_translate("DiskLoad");
                $("#Plugin_DiskLoad").show();
            }
        }
    });
}

$(document).ready(function diskload_buildpage() {
    $("#footer").before(buildBlock("DiskLoad", 1, true));
    $("#Plugin_DiskLoad").addClass("halfsize");

    diskload_buildTable();

    diskload_request();

    $("#Reload_DiskLoadTable").click(function diskload_reload(id) {
        diskload_request();
        $(this).attr("title", datetime());
    });
});
