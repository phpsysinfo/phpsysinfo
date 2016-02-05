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
// $Id: snmppinfo.js 661 2012-08-27 11:26:39Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, createBar, genlang */

"use strict";

var snmppinfo_show = false;

/**
 * build the table where content is inserted
 * @param {jQuery} xml plugin-XML
 */
function snmppinfo_buildTable(xml) {
    var html = "", tree = [], closed = [];

    $("#Plugin_SNMPPInfo #Plugin_SNMPPInfoTable").remove();

    html += "  <table id=\"Plugin_SNMPPInfoTable\" class=\"tablemain\" style=\"width:100%;\">\n";
    html += "   <thead>\n";
    html += "    <tr>\n";
    html += "     <th>" + genlang(3, false, "SNMPPInfo") + "</th>\n";
    html += "     <th style=\"width:120px;\">" + genlang(4, false, "SNMPPInfo") + "</th>\n";
    html += "     <th style=\"width:80px;\">" + genlang(5, false, "SNMPPInfo") + "</th>\n";
    html += "    </tr>\n";
    html += "   </thead>\n";
    html += "   <tbody class=\"tree\">\n";

    var lastdev="", index = 0 ;
    $("Plugins Plugin_SNMPPInfo Printer MarkerSupplies", xml).each(function snmppinfo_getprinters(id) {
        var close = 0, name = "", device = "", desc = "", unit = 0, max = 0, level = 0, percent = 0, units = "";
        name = $(this).parent().attr("Name");
        device = $(this).parent().attr("Device");
        desc = $(this).attr("Description");

        unit = parseInt($(this).attr("SupplyUnit"), 10);
        max = parseInt($(this).attr("MaxCapacity"), 10);
        level = parseInt($(this).attr("Level"), 10);

        if (max>0 && (level>=0) && (level<=max) ) {
            percent = Math.round(100*level/max);
            units = level+" / "+max;
        } else if (max==-2 && (level>=0) && (level<=100) ) {
            percent = level;
            units = level+" / 100";
        } else if (level==-3) {
            percent = 100;
            units = genlang(6, false, "SNMPPInfo")
        } else {
            percent = 0;
            units = genlang(7, false, "SNMPPInfo")
        }

        if (device!=lastdev) {
            html += "    <tr><td><span class=\"treespanbold\">" + device + " (" + name + ") </span></td></tr>\n";
            index = tree.push(0);
            lastdev = device;
        }
        html += "    <tr><td><span class=\"treespan\">" + desc + "</span></td><td>" + createBar(percent) +"</td><td>" + units +"</td></tr>\n";

        tree.push(index);
        snmppinfo_show = true;
    });

    html += "   </tbody>\n";
    html += "  </table>\n";

    $("#Plugin_SNMPPInfo").append(html);

    $("#Plugin_SNMPPInfoTable").jqTreeTable(tree, {
        openImg: "./gfx/treeTable/tv-collapsable.gif",
        shutImg: "./gfx/treeTable/tv-expandable.gif",
        leafImg: "./gfx/treeTable/tv-item.gif",
        lastOpenImg: "./gfx/treeTable/tv-collapsable-last.gif",
        lastShutImg: "./gfx/treeTable/tv-expandable-last.gif",
        lastLeafImg: "./gfx/treeTable/tv-item-last.gif",
        vertLineImg: "./gfx/treeTable/vertline.gif",
        blankImg: "./gfx/treeTable/blank.gif",
        collapse: closed,
        column: 0,
        striped: true,
        highlight: false,
        state: false
    });

}

/**
 * load the xml via ajax
 */
function snmppinfo_request() {
    $("#Reload_SNMPPInfoTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=SNMPPInfo",
        dataType: "xml",
        error: function snmppinfo_error() {
            $.jGrowl("Error loading XML document for Plugin SNMPPInfo!");
        },
        success: function snmppinfo_buildblock(xml) {
            populateErrors(xml);
            snmppinfo_buildTable(xml);
            if (snmppinfo_show) {
                plugin_translate("SNMPPInfo");
                $("#Plugin_SNMPPInfo").show();
            }
        }
    });
}

$(document).ready(function snmppinfo_buildpage() {
    $("#footer").before(buildBlock("SNMPPInfo", 1, true));
    $("#Plugin_SNMPPInfo").css("width", "451px");

    snmppinfo_request();

    $("#Reload_SNMPPInfoTable").click(function snmppinfo_reload(id) {
        snmppinfo_request();
        $(this).attr("title", datetime());
    });
});
