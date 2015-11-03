function renderPlugin_smart(data) {

    if ((data['Plugins']['Plugin_SMART'] !== undefined) && (data['Plugins']['Plugin_SMART']["columns"] !== undefined) && (items(data['Plugins']['Plugin_SMART']["columns"]["column"]).length > 0)
            && (data['Plugins']['Plugin_SMART']["disks"] !== undefined) && (items(data['Plugins']['Plugin_SMART']["disks"]["disk"]).length > 0)) {
        var smartitems = items(data['Plugins']['Plugin_SMART']["columns"]["column"]);
        var smartnames = {
            1:"plugin_smart_101",	// "Raw Read Error Rate",		
            2:"plugin_smart_102",	// "Throughput Performance",
            3:"plugin_smart_103",	// "Spin Up Time",
            4:"plugin_smart_104",	// "Start Stop Count",
            5:"plugin_smart_105",	// "Reallocated Sector Ct",
            7:"plugin_smart_106",	// "Seek Error Rate",
            8:"plugin_smart_108",	// "Seek Time Performance",
            9:"plugin_smart_109",	// "Power On Hours",
            10:"plugin_smart_110",	// "Spin Retry Count",
            11:"plugin_smart_111",	// "Calibration Retry Count",
            12:"plugin_smart_112",	// "Power Cycle Count",
            190:"plugin_smart_290",	// "Airflow Temperature",
            191:"plugin_smart_291",	// "G-sense Error Rate",
            192:"plugin_smart_292",	// "Power-Off Retract Count",
            193:"plugin_smart_293",	// "Load Cycle Count",
            194:"plugin_smart_294",	// "Temperature",
            195:"plugin_smart_295",	// "Hardware ECC Recovered",
            196:"plugin_smart_296",  // "Reallocated Event Count",
            197:"plugin_smart_297",  // "Current Pending Sector",
            198:"plugin_smart_298",  // "Offline Uncorr.",
            199:"plugin_smart_299",  // "UDMA CRC Error Count",
            200:"plugin_smart_300",  // "Multi Zone Error Rate",
            201:"plugin_smart_301",  // "Soft Read Error Rate",
            202:"plugin_smart_302",  // "Data Address Mark Errors",
            223:"plugin_smart_323",  // "Load Retry Count",
            225:"plugin_smart_325",  };	// "Load Cycle Count"
        
        var html = '';

        html+="<thead>";
        html+="<tr>";
        html+="<th id=\"smart_name\" class=\"rightCell\">"+genlang(3, false, 'smart')+"</th>";	// Name
        for (var i = 0; i < smartitems.length ; i++) {
            smartid = smartitems[i]["@attributes"]["id"];
            if (smartnames[smartid] !== undefined) {
                html+="<th class=\"sorttable_numeric rightCell\">"+ genlang(100+parseInt(smartid), false, 'smart') + "</th>";
            } else {
                html+="<th class=\"sorttable_numeric rightCell\">"+ smartid + "</th>";
            }
        }
        html+="</tr>";
        html+="</thead>";

        var diskitems = items(data['Plugins']['Plugin_SMART']["disks"]["disk"]);
        html += '<tbody>';
        for (var i = 0; i < diskitems.length; i++) {
            html += '<tr>';
            html += '<th class="rightCell">'+ diskitems[i]["@attributes"]["name"] + '</th>';
            attribitems = items(diskitems[i]["attribute"]);
            var valarray = [];
            for (var j = 0;j < attribitems.length; j++) {
                valarray[attribitems[j]["@attributes"]["id"]] = attribitems[j]["@attributes"]["raw_value"];
            }
            for (var j = 0; j < smartitems.length; j++) {
                var smartid = smartitems[j]["@attributes"]["id"];
                var itemvalue = valarray[smartid];
                if ((itemvalue !== undefined) && (itemvalue !== '' )) {
                    if (smartid === "194") {
                        html += '<td class="rightCell">' + formatTemp(itemvalue, data["Options"]["@attributes"]["tempFormat"]) + '</td>';
                    } else {
                        html += '<td class="rightCell">' + itemvalue + '</td>';
                    }
                } else {
                    html += '<td></td>';
                }
            }
            html += '</tr>'; 
        }
        html += '</tbody>';
        $('#smart').empty().append(html);
        $('#smart').addClass("sortable");
        sorttable.makeSortable($('#smart')[0]);
        sorttable.innerSortFunction.apply($('#smart_name')[0], []);
        $('#block_smart').show();
    } else {
        $('#block_smart').hide();
    }
}
