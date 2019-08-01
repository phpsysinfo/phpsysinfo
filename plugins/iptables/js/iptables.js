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

//$Id: iptables.js 661 2016-05-03 11:26:39 erpomata $


/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang, createBar */

"use strict";

var iptables_show = false;

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */

function iptables_populate(xml) {

    var html = "";
 
    $("Plugins Plugin_iptables iptables Item", xml).each(function iptables_getitem(idp) {
        html += "      <tr>\n";
        html += "        <td style=\"font-weight:normal\">" +  $(this).attr("Rule") + "</td>\n";
        html += "      </tr>\n";
        iptables_show = true;
    });

    $("#Plugin_iptablesTable-tbody").empty().append(html);
    $('#Plugin_iptablesTable tr:nth-child(even)').addClass('even');

}

function iptables_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_iptablesTable\" class=\"stripeMe\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(101, "iptables") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody id=\"Plugin_iptablesTable-tbody\">\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";

    $("#Plugin_iptables").append(html);
}

/**
 * load the xml via ajax
 */

function iptables_request() {
    $("#Reload_iptablesTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=iptables",
        dataType: "xml",
        error: function iptables_error() {
            $.jGrowl("Error loading XML document for Plugin iptables!");
        },
        success: function iptables_buildblock(xml) {
            populateErrors(xml);
            iptables_populate(xml);
            if (iptables_show) {
                plugin_translate("iptables");
                $("#Plugin_iptables").show();
            }
        }
    });
}

$(document).ready(function iptables_buildpage() {
    $("#footer").before(buildBlock("iptables", 1, true));
    $("#Plugin_iptables").css("width", "915px");

    iptables_buildTable();

    iptables_request();

    $("#Reload_iptablesTable").click(function iptables_reload(id) {
        iptables_request();
        $(this).attr("title", datetime());
    });
});
