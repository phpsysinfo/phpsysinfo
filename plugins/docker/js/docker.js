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

//$Id: docker.js 661 2014-01-08 11:26:39 aolah76 $


/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang, createBar */

"use strict";

var docker_show = false, docker_table;

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */

function docker_populate(xml) {

    var html = "";

    docker_table.fnClearTable();

    $("Plugins Plugin_Docker Docker Item", xml).each(function docker_getitem(id) {
        var name = "", cpuu= 0, memu = 0, used = 0, limit = 0, netio = "", blockio = "", pids = 0;
        name = $(this).attr("Name");
        cpuu = parseInt($(this).attr("CPUUsage"), 10);
        memu = parseInt($(this).attr("MemoryUsage"), 10);
        used = parseInt($(this).attr("MemoryUsed"), 10);
        limit = parseInt($(this).attr("MemoryLimit"), 10);
        netio = $(this).attr("NetIO");
        blockio = $(this).attr("BlockIO");
        pids = parseInt($(this).attr("PIDs"), 10);

        docker_table.fnAddData(["<span style=\"display:none;\">" + name + "</span>" + name, "<span style=\"display:none;\">" + cpuu + "</span>" + createBar(cpuu), "<span style=\"display:none;\">" + memu + "</span>" + createBar(memu), "<span style=\"display:none;\">" + used + "</span>" + formatBytes(used, xml), "<span style=\"display:none;\">" + limit + "</span>" + formatBytes(limit, xml), "<span style=\"display:none;\">" + netio + "</span>" + netio, "<span style=\"display:none;\">" + blockio + "</span>" + blockio, "<span style=\"display:none;\">" + pids + "</span>" + pids]);
        docker_show = true;
    });
}

function docker_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_DockerTable\" class=\"stripeMe\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(101, "docker") + "</th>\n";
    html += "        <th>" + genlang(102, "docker") + "</th>\n";
    html += "        <th>" + genlang(103, "docker") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(104, "docker") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(105, "docker") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(106, "docker") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(107, "docker") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(108, "docker") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody id=\"Plugin_DockerTable-tbody\">\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";
    $("#Plugin_Docker").append(html);

    docker_table = $("#Plugin_DockerTable").dataTable({
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
        }, {
            "sType": 'span-number'
        }, {
            "sType": 'span-number',
            "sClass": "right"
        }, {
            "sType": 'span-number',
            "sClass": "right"
        }, {
            "sType": 'span-string',
            "sClass": "right"
        }, {
            "sType": 'span-string',
            "sClass": "right"
        }, {
            "sType": 'span-number',
            "sClass": "right"
        }]
    });
}

/**
 * load the xml via ajax
 */

function docker_request() {
    $("#Reload_DockerTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=docker",
        dataType: "xml",
        error: function docker_error() {
            $.jGrowl("Error loading XML document for Plugin docker!");
        },
        success: function docker_buildblock(xml) {
            populateErrors(xml);
            docker_populate(xml);
            if (docker_show) {
                plugin_translate("Docker");
                $("#Plugin_Docker").show();
            }
        }
    });
}

$(document).ready(function docker_buildpage() {
    $("#footer").before(buildBlock("Docker", 1, true));
    $("#Plugin_Docker").css("width", "915px");

    docker_buildTable();

    docker_request();

    $("#Reload_DockerTable").click(function docker_reload(id) {
        docker_request();
        $(this).attr("title", datetime());
    });
});
