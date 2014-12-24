function renderPlugin_SMART(data) {

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
        
        $('#smart-th').append('<th id="smart_name" class="rightCell">Name</th>');
        for (i = 0; i < smartitems.length ; i++) {
            smartid = smartitems[i]["@attributes"]["id"];
            if (smartnames[smartid] !== undefined) {
                $('#smart-th').append('<th class="rightCell">'+ smartnames[smartid] + '</th>');
            } else {
                $('#smart-th').append('<th class="rightCell">'+ smartid + '</th>');
            }
        }
        
        var diskitems = items(data['Plugins']['Plugin_SMART']["disks"]["disk"]);
        var html = '';
        html += '<tbody>';
        for (i = 0; i < diskitems.length ; i++) {
            html += '<tr>';
            html += '<th class="rightCell">'+ diskitems[i]["@attributes"]["name"] + '</th>';
            for (j = 0; j < smartitems.length; j++) {
                smartid = smartitems[j]["@attributes"]["id"];
                attribitems = items(diskitems[i]["attribute"]);
                var itemvalue = '';
                for (k = 0; k < attribitems.length ; k++) {
                    if (attribitems[k]["@attributes"]["id"] == smartid) {
                        itemvalue = attribitems[k]["@attributes"]["raw_value"];
                        break;
                    }
                }
                if (itemvalue !== '' ) {
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
        $('#smart').append(html);
//        $('#smart').addClass("sortable");
        sorttable.makeSortable($('#smart')[0]);
        sorttable.innerSortFunction.apply($('#smart_name')[0], []);
        $('#block_smart').show();
    }
}
