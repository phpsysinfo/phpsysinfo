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
// $Id: ps.js 661 2012-08-27 11:26:39Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, createBar, genlang */

"use strict";

var ps_show = false;

/**
 * build the table where content is inserted
 * @param {jQuery} xml plugin-XML
 */
function ps_buildTable(xml) {
    var html = "", tree = [], closed = [2];
    
    $("#Plugin_PS #Plugin_PSTable").remove();

    html += "  <table id=\"Plugin_PSTable\" class=\"tablemain\" style=\"width:100%;\">\n";
    html += "   <thead>\n";
    html += "    <tr>\n";
    html += "     <th>" + genlang(3, false, "PS") + "</th>\n";
    html += "     <th style=\"width:80px;\">" + genlang(4, false, "PS") + "</th>\n";
    html += "     <th style=\"width:80px;\">" + genlang(5, false, "PS") + "</th>\n";
    html += "     <th style=\"width:110px;\">" + genlang(6, false, "PS") + "</th>\n";
    html += "    </tr>\n";
    html += "   </thead>\n";
    html += "   <tbody class=\"tree\">\n";

    $("Plugins Plugin_PS Process", xml).each(function ps_getprocess(id) {
        var close = 0, pid = 0, ppid = 0, name = "", percent = 0, parentId = 0, expanded = 0;
        name = $(this).attr("Name");
        parentId = parseInt($(this).attr("ParentID"), 10);
        pid = parseInt($(this).attr("PID"), 10);
        ppid = parseInt($(this).attr("PPID"), 10);
        percent = parseInt($(this).attr("MemoryUsage"), 10);
        expanded = parseInt($(this).attr("Expanded"), 10);

        html += "    <tr><td><span class=\"treespan\">" + name + "</span></td><td>" + pid + "</td><td>" + ppid + "</td><td>" + createBar(percent) + "</td></tr>\n";
        close = tree.push(parentId);
        if (!isNaN(expanded) && (expanded === 0)) {
            closed.push(close);
        }
        ps_show = true;
    });

    html += "   </tbody>\n";
    html += "  </table>\n";

    $("#Plugin_PS").append(html);

    $("#Plugin_PSTable").jqTreeTable(tree, {
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
function ps_request() {
    $.ajax({
        url: "xml.php?plugin=PS",
        dataType: "xml",
        error: function ps_error() {
            $.jGrowl("Error loading XML document for Plugin PS!");
        },
        success: function ps_buildblock(xml) {
            populateErrors(xml);
            ps_buildTable(xml);
            if (ps_show) {
                plugin_translate("PS");
                $("#Reload_PSTable").attr("title",datetime());
                $("#Plugin_PS").show();
            }
        }
    });
}

$(document).ready(function ps_buildpage() {
    $("#footer").before(buildBlock("PS", 1, true));
    $("#Plugin_PS").css("width", "915px");

    ps_request();

    $("#Reload_PSTable").click(function ps_reload(id) {
        ps_request();
    });
});
