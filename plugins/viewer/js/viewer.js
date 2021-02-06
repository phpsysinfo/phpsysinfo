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

//$Id: viewer.js 661 2016-05-03 11:26:39 erpomata $


/*global $, jQuery, buildBlock, datetime, plugin_translate */

"use strict";

var viewer_show = false;

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */

function viewer_populate(xml) {
    var html = "", name = "", hostname = "";
 
    hostname = $("Plugins Plugin_Viewer", xml).attr('Hostname');
    if (hostname !== undefined) {
        $('span[class=Hostname_Viewer]').html(hostname);
    }

    name = $("Plugins Plugin_Viewer Viewer", xml).attr("Name");
    $("#Plugin_viewerTable-th").empty();
    if (name !== undefined) $("#Plugin_viewerTable-th").append(name);

    $("Plugins Plugin_Viewer Viewer Item", xml).each(function viewer_getitem(idp) {
        html += "      <tr>\n";
        if ($(this).attr("Line") === "")
            html += "        <td style=\"font-weight:normal\">&nbsp;</td>\n";
        else
            html += "        <td style=\"font-weight:normal\">" +  $(this).attr("Line") + "</td>\n";
        html += "      </tr>\n";
        viewer_show = true;
    });

    $("#Plugin_viewerTable-tbody").empty().append(html);
    $('#Plugin_viewerTable tr:nth-child(even)').addClass('even');

}

function viewer_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_viewerTable\" class=\"stripeMe\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th id=\"Plugin_viewerTable-th\"></th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody id=\"Plugin_viewerTable-tbody\">\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";

    $("#Plugin_viewer").append(html);
}

/**
 * load the xml via ajax
 */

function viewer_request() {
    $("#Reload_viewerTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=viewer",
        dataType: "xml",
        error: function viewer_error() {
            $.jGrowl("Error loading XML document for Plugin Viewer!");
        },
        success: function viewer_buildblock(xml) {
            populateErrors(xml);
            viewer_populate(xml);
            if (viewer_show) {
                plugin_translate("viewer");
                $("#Plugin_viewer").show();
            }
        }
    });
}

$(document).ready(function viewer_buildpage() {
    $("#footer").before(buildBlock("viewer", 1, true));
    $("#Plugin_viewer").addClass("fullsize");

    viewer_buildTable();

    viewer_request();

    $("#Reload_viewerTable").click(function viewer_reload(id) {
        viewer_request();
        $(this).attr("title", datetime());
    });
});
