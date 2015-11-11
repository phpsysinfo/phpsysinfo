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

//$Id: ipmiinfo.js 661 2012-08-27 11:26:39Z namiltd $


/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang, createBar */

"use strict";

var ipmiinfo_show = false;
/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function ipmiinfo_populate(xml) {

    var html = "";
    $("#Plugin_ipmiinfoTable").html(" ");

    $("Plugins Plugin_ipmiinfo Temperatures Item", xml).each(function ipmiinfo_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(3, true, "ipmiinfo") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmiinfo_show = true;
    });

    $("Plugins Plugin_ipmiinfo Fans Item", xml).each(function ipmiinfo_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(4, true, "ipmiinfo") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmiinfo_show = true;
    });

    $("Plugins Plugin_ipmiinfo Voltages Item", xml).each(function ipmiinfo_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(5, true, "ipmiinfo") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmiinfo_show = true;
    });

    $("Plugins Plugin_ipmiinfo Currents Item", xml).each(function ipmiinfo_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(7, true, "ipmiinfo") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmiinfo_show = true;
    });

    $("Plugins Plugin_ipmiinfo Powers Item", xml).each(function ipmiinfo_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(8, true, "ipmiinfo") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmiinfo_show = true;
    });
    $("Plugins Plugin_ipmiinfo Misc Item", xml).each(function ipmiinfo_getitem(idp) {
        if(idp==0) {
            html += "<tr><th colspan=\"2\" style=\"font-weight:bold\">" + genlang(6, true, "ipmiinfo") + "</th></tr>\n";
        }
        html += "    <tr>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Label") + "</td>\n";
        html += "      <td style=\"font-weight:normal\">" +  $(this).attr("Value") + "</td>\n";
        html += "    </tr>\n";
        ipmiinfo_show = true;
    });

    $("#Plugin_ipmiinfoTable").append(html);
    $('#Plugin_ipmiinfoTable tr:nth-child(even)').addClass('even');

}

function ipmiinfo_buildTable() {
    var html = "";

    html += "<table id=\"Plugin_ipmiinfoTable\" class=\"stripeMe\" style=\"border-spacing:0;\">\n";
    html += "  <thead>\n";
    html += "  </thead>\n";
    html += "  <tbody>\n";
    html += "  </tbody>\n";
    html += "</table>\n";
    $("#Plugin_ipmiinfo").append(html);
}

/**
 * load the xml via ajax
 */
function ipmiinfo_request() {
    $.ajax({
        url: "xml.php?plugin=ipmiinfo",
        dataType: "xml",
        error: function ipmiinfo_error() {
        $.jGrowl("Error loading XML document for Plugin ipmiinfo!");
    },
    success: function ipmiinfo_buildblock(xml) {
        populateErrors(xml);
        ipmiinfo_populate(xml);
        if (ipmiinfo_show) {
            plugin_translate("ipmiinfo");
            $("#Reload_ipmiinfoTable").attr("title",datetime());
            $("#Plugin_ipmiinfo").show();
        }
    }
    });
}

$(document).ready(function ipmiinfo_buildpage() {
    $("#footer").before(buildBlock("ipmiinfo", 1, true));
    $("#Plugin_ipmiinfo").css("width", "451px");

    ipmiinfo_buildTable();

    ipmiinfo_request();

    $("#Reload_ipmiinfoTable").click(function ipmiinfo_reload(id) {
        ipmiinfo_request();
    });
});
