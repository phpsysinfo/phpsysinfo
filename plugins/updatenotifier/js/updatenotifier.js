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

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang, createBar */

"use strict";

var UpdateNotifier_show = false, UpdateNotifier_table;
/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function updatenotifier_populate(xml) {
    var html = "";
 
    $("Plugins Plugin_UpdateNotifier UpdateNotifier", xml).each(function(idp) {
        var packages = "", security = "";
        packages = $("packages", this).text();
        security = $("security", this).text();

        //UpdateNotifier_table.fnAddData([packages]);
        //UpdateNotifier_table.fnAddData([security]);

        html  = "  <tr>\n";
        html += "    <td>" + packages + " " + genlang(3, true, "UpdateNotifier") + "</td>\n";
        html += "  </tr>\n";
        html += "  <tr>\n";
        html += "    <td>" + security + " " + genlang(4, true, "UpdateNotifier") + "</td>\n";
        html += "  </tr>\n";

        $("#Plugin_UpdateNotifier tbody").empty().append(html);

        if ((packages == 0) && (security == 0)) {
            $("#UpdateNotifierTable-info").html(genlang(5, true, "UpdateNotifier"));
        } else {
            $("#UpdateNotifierTable-info").html(genlang(2, true, "UpdateNotifier"));
        }

        UpdateNotifier_show = true;
    });
}

/**
 * fill the plugin block with table structure
 */
function updatenotifier_buildTable() {
    var html = "";

    html += "<table id=\"Plugin_UpdateNotifierTable\" style=\"border-spacing:0;\">\n";
    html += "  <thead>\n";
    html += "    <tr>\n";
    html += "      <th id=\"UpdateNotifierTable-info\">" + genlang(2, true, "UpdateNotifier") + "</th>\n";
    html += "    </tr>\n";
    html += "  </thead>\n";
    html += "  <tbody>\n";
    html += "  </tbody>\n";
    html += "</table>\n";

    $("#Plugin_UpdateNotifier").append(html);

}

/**
 * load the xml via ajax
 */
function updatenotifier_request() {
    $.ajax({
        url: "xml.php?plugin=UpdateNotifier",
        dataType: "xml",
        error: function () {
        $.jGrowl("Error loading XML document for Plugin UpdateNotifier!");
    },
    success: function updatenotifier_buildblock(xml) {
        populateErrors(xml);
        updatenotifier_populate(xml);
        if (UpdateNotifier_show) {
            plugin_translate("UpdateNotifier");
            $("#Reload_UpdateNotifierTable").attr("title",datetime());
            $("#Plugin_UpdateNotifier").show();
        }
    }
    });
}

$(document).ready(function() {
    $("#footer").before(buildBlock("UpdateNotifier", 1, true));
    $("#Plugin_UpdateNotifier").css("width", "451px");

    updatenotifier_buildTable();
    updatenotifier_request();

    $("#Reload_UpdateNotifierTable").click(function updatenotifier_reload(id) {
        updatenotifier_request();
    });
});
