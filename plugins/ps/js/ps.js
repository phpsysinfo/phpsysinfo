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
    var html = "", tree = [], closed = [], memwas = false, cpuwas = false;

    $("#Plugin_PS #Plugin_PSTable").remove();

    html += "  <div style=\"overflow-x:auto;\">\n";
    html += "    <table id=\"Plugin_PSTable\" class=\"tablemain\">\n";
    html += "      <thead>\n";
    html += "        <tr>\n";
    html += "          <th>" + genlang(2, "PS") + "</th>\n";
    html += "          <th style=\"width:40px;\">" + genlang(3, "PS") + "</th>\n";
    html += "          <th style=\"width:40px;\">" + genlang(4, "PS") + "</th>\n";
    html += "          <th style=\"width:120px;\">" + genlang(5, "PS") + "</th>\n";
    html += "          <th style=\"width:120px;\">" + genlang(6, "PS") + "</th>\n";
    html += "        </tr>\n";
    html += "      </thead>\n";
    html += "      <tbody class=\"tree\">\n";

    $("Plugins Plugin_PS Process", xml).each(function ps_getprocess(id) {
        var close = 0, pid = 0, ppid = 0, name = "", percent = 0, parentId = 0, expanded = 0, cpu = 0;
        name = $(this).attr("Name").replace(/,/g, ",<wbr>").replace(/\s/g, " <wbr>").replace(/\./g, ".<wbr>").replace(/-/g, "<wbr>-").replace(/\//g, "<wbr>/"); /* split long name */
        parentId = parseInt($(this).attr("ParentID"), 10);
        pid = parseInt($(this).attr("PID"), 10);
        ppid = parseInt($(this).attr("PPID"), 10);
        percent = parseInt($(this).attr("MemoryUsage"), 10);
        cpu = parseInt($(this).attr("CPUUsage"), 10);
        expanded = parseInt($(this).attr("Expanded"), 10);

        html += "        <tr><td><div class=\"treediv\"><span class=\"treespan\">" + name + "</div></span></td><td>" + pid + "</td><td>" + ppid + "</td><td>" + createBar(percent) + "</td><td>" + createBar(cpu) + "</td></tr>\n";
        close = tree.push(parentId);
        if (!isNaN(expanded) && (expanded === 0)) {
            closed.push(close);
        }
        if (!memwas && !isNaN(percent)) {
            memwas = true;
        }
        if (!cpuwas && !isNaN(cpu)) {
            cpuwas = true;
        }
        ps_show = true;
    });

    html += "      </tbody>\n";
    html += "    </table>\n";
    html += "  </div>\n";

    $("#Plugin_PS").append(html);

    if (memwas) {
        $('#Plugin_PSTable td:nth-child(4),#Plugin_PSTable th:nth-child(4)').show();
    } else {
        $('#Plugin_PSTable td:nth-child(4),#Plugin_PSTable th:nth-child(4)').hide();
    }
    if (cpuwas) {
        $('#Plugin_PSTable td:nth-child(5),#Plugin_PSTable th:nth-child(5)').show();
    } else {
        $('#Plugin_PSTable td:nth-child(5),#Plugin_PSTable th:nth-child(5)').hide();
    }

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
    $("#Reload_PSTable").attr("title", "reload");
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
        $(this).attr("title", datetime());
    });
});
