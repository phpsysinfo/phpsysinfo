function renderPlugin_smart(data) {

    if ((data['Plugins']['Plugin_SMART'] !== undefined) && (data['Plugins']['Plugin_SMART']["columns"] !== undefined) && (items(data['Plugins']['Plugin_SMART']["columns"]["column"]).length > 0)
            && (data['Plugins']['Plugin_SMART']["disks"] !== undefined) && (items(data['Plugins']['Plugin_SMART']["disks"]["disk"]).length > 0)) {
        var smartitems = items(data['Plugins']['Plugin_SMART']["columns"]["column"]);
        var smartnames = {
            1:"Raw Read Error Rate",
            2:"Throughput Performance",
            3:"Spin Up Time",
            4:"Start Stop Count",
            5:"Reallocated Sector Ct",
            7:"Seek Error Rate",
            8:"Seek Time Performance",
            9:"Power On Hours",
            10:"Spin Retry Count",
            11:"Calibration Retry Count",
            12:"Power Cycle Count",
            190:"Airflow Temperature",
            191:"G-sense Error Rate",
            192:"Power-Off Retract Count",
            193:"Load Cycle Count",
            194:"Temperature",
            195:"Hardware ECC Recovered",
            196:"Reallocated Event Count",
            197:"Current Pending Sector",
            198:"Offline Uncorr.",
            199:"UDMA CRC Error Count",
            200:"Multi Zone Error Rate",
            201:"Soft Read Error Rate",
            202:"Data Address Mark Errors",
            223:"Load Retry Count",
            225:"Load Cycle Count"};
        
        var html = '';

        html+="<thead>";
        html+="<tr>";
        html+="<th id=\"smart_name\" class=\"rightCell\">Name</th>";
        for (i = 0; i < smartitems.length ; i++) {
            smartid = smartitems[i]["@attributes"]["id"];
            if (smartnames[smartid] !== undefined) {
                html+="<th class=\"sorttable_numeric rightCell\">"+ smartnames[smartid] + "</th>";
            } else {
                html+="<th class=\"sorttable_numeric rightCell\">"+ smartid + "</th>";
            }
        }
        html+="</tr>";
        html+="</thead>";

        var diskitems = items(data['Plugins']['Plugin_SMART']["disks"]["disk"]);
        html += '<tbody>';
        for (i = 0; i < diskitems.length; i++) {
            html += '<tr>';
            html += '<th class="rightCell">'+ diskitems[i]["@attributes"]["name"] + '</th>';
            attribitems = items(diskitems[i]["attribute"]);
            var valarray = [];
            for (j = 0;j < attribitems.length; j++) {
                valarray[attribitems[j]["@attributes"]["id"]] = attribitems[j]["@attributes"]["raw_value"];
            }
            for (j = 0; j < smartitems.length; j++) {
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
