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

//$Id: ipmi.js 518 2011-10-28 08:09:07Z namiltd $


/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang, createBar */

"use strict";

var ipmi_show = false;
/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function ipmi_populate(xml) {

    var html = "";
    $("#Plugin_ipmiTable").html(" ");

    $("Plugins Plugin_ipmi Temperature Item", xml).each(function ipmi_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(3, true, "ipmi") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmi_show = true;
    });

    $("Plugins Plugin_ipmi Fans Item", xml).each(function ipmi_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(4, true, "ipmi") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmi_show = true;
    });

    $("Plugins Plugin_ipmi Voltage Item", xml).each(function ipmi_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(5, true, "ipmi") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmi_show = true;
    });

    $("Plugins Plugin_ipmi Misc Item", xml).each(function ipmi_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(6, true, "ipmi") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmi_show = true;
    });

    $("#Plugin_ipmiTable").append(html);
    $('#Plugin_ipmiTable tr:nth-child(even)').addClass('even');

}

function ipmi_buildTable() {
    var html = "";

    html += "<table id=\"Plugin_ipmiTable\" class=\"stripeMe\" style=\"border-spacing:0;\">\n";
    html += "  <thead>\n";
    html += "  </thead>\n";
    html += "  <tbody>\n";
    html += "  </tbody>\n";
    html += "</table>\n";
    $("#Plugin_ipmi").append(html);
}

/**
 * load the xml via ajax
 */
function ipmi_request() {
    $.ajax({
        url: "xml.php?plugin=ipmi",
        dataType: "xml",
        error: function ipmi_error() {
        $.jGrowl("Error loading XML document for Plugin ipmi!");
    },
    success: function ipmi_buildblock(xml) {
        populateErrors(xml);
        ipmi_populate(xml);
        if (ipmi_show) {
            plugin_translate("ipmi");
            $("#Plugin_ipmi").show();
        }
    }
    });
}

$(document).ready(function ipmi_buildpage() {
    $("#footer").before(buildBlock("ipmi", 1, true));
    $("#Plugin_ipmi").css("width", "451px");

    ipmi_buildTable();

    ipmi_request();

    $("#Reload_ipmiTable").click(function ipmi_reload(id) {
        ipmi_request();
        $("#Reload_ipmiTable").attr("title",datetime());
    });
});
