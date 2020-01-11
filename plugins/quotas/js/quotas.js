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
// $Id: quotas.js 661 2012-08-27 11:26:39Z namiltd $
//

/*global $, jQuery, buildBlock, datetime, plugin_translate, genlang, formatBytes, createBar */

"use strict";

var quotas_show = false, quotas_table;

//appendcss("./plugins/Quotas/css/Quotas.css");

/**
 * insert content into table
 * @param {jQuery} xml plugin-XML
 */
function quotas_populate(xml) {
    quotas_table.fnClearTable();

    $("Plugins Plugin_Quotas Quota", xml).each(function quotas_getquota(id) {
        var user = "", bused = 0, bsoft = 0, bhard = 0, bpuse = 0, fpuse = 0, fused = 0, fsoft = 0, fhard = 0;
        user = $(this).attr("User");
        bused = parseInt($(this).attr("ByteUsed"), 10);
        bsoft = parseInt($(this).attr("ByteSoft"), 10);
        bhard = parseInt($(this).attr("ByteHard"), 10);
        bpuse = parseInt($(this).attr("BytePercentUsed"), 10);
        fused = parseInt($(this).attr("FileUsed"), 10);
        fsoft = parseInt($(this).attr("FileSoft"), 10);
        fhard = parseInt($(this).attr("FileHard"), 10);
        fpuse = parseInt($(this).attr("FilePercentUsed"), 10);

        quotas_table.fnAddData(["<span style=\"display:none;\">" + user + "</span>" + user, "<span style=\"display:none;\">" + bused + "</span>" + formatBytes(bused, xml), "<span style=\"display:none;\">" + bsoft + "</span>" + formatBytes(bsoft, xml), "<span style=\"display:none;\">" + bhard + "</span>" + formatBytes(bhard, xml), "<span style=\"display:none;\">" + bpuse + "</span>" + createBar(bpuse), "<span style=\"display:none;\">" + fused + "</span>" + fused, "<span style=\"display:none;\">" + fsoft + "</span>" + fsoft, "<span style=\"display:none;\">" + fhard + "</span>" + fhard, "<span style=\"display:none;\">" + fpuse + "</span>" + createBar(fpuse)]);
        quotas_show = true;
    });
}

/**
 * fill the plugin block with table structure
 */
function quotas_buildTable() {
    var html = "";

    html += "<div style=\"overflow-x:auto;\">\n";
    html += "  <table id=\"Plugin_QuotasTable\" style=\"border-collapse:collapse;\">\n";
    html += "    <thead>\n";
    html += "      <tr>\n";
    html += "        <th>" + genlang(2, "Quotas") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(3, "Quotas") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(4, "Quotas") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(5, "Quotas") + "</th>\n";
    html += "        <th>" + genlang(6, "Quotas") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(7, "Quotas") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(8, "Quotas") + "</th>\n";
    html += "        <th class=\"right\">" + genlang(9, "Quotas") + "</th>\n";
    html += "        <th>" + genlang(10, "Quotas") + "</th>\n";
    html += "      </tr>\n";
    html += "    </thead>\n";
    html += "    <tbody>\n";
    html += "    </tbody>\n";
    html += "  </table>\n";
    html += "</div>\n";

    $("#Plugin_Quotas").append(html);

    quotas_table = $("#Plugin_QuotasTable").dataTable({
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
            "sType": 'span-number'
        }, {
            "sType": 'span-number'
        }, {
            "sType": 'span-number'
        }, {
            "sType": 'span-number'
        }, {
            "sType": 'span-number'
        }, {
            "sType": 'span-number'
        }]
    });
}

/**
 * load the xml via ajax
 */
function quotas_request() {
    $("#Reload_QuotasTable").attr("title", "reload");
    $.ajax({
        url: "xml.php?plugin=Quotas",
        dataType: "xml",
        error: function quotas_error() {
            $.jGrowl("Error loading XML document for Plugin quotas!");
        },
        success: function quotas_buildblock(xml) {
            populateErrors(xml);
            quotas_populate(xml);
            if (quotas_show) {
                plugin_translate("Quotas");
                $("#Plugin_Quotas").show();
            }
        }
    });
}

$(document).ready(function quotas_buildpage() {
    $("#footer").before(buildBlock("Quotas", 1, true));
    $("#Plugin_Quotas").css("width", "915px");

    quotas_buildTable();

    quotas_request();

    $("#Reload_QuotasTable").click(function quotas_reload(id) {
        quotas_request();
        $(this).attr("title", datetime());
    });
});
