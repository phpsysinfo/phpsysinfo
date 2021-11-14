function renderPlugin_smart(data) {

    if ((data.Plugins.Plugin_SMART !== undefined) && (data.Plugins.Plugin_SMART.columns !== undefined) && (items(data.Plugins.Plugin_SMART.columns.column).length > 0) && (data.Plugins.Plugin_SMART.disks !== undefined) && (items(data.Plugins.Plugin_SMART.disks.disk).length > 0)) {
        var smartitems = items(data.Plugins.Plugin_SMART.columns.column);
        var html = '';
        var i,j;
        var smartid;

        html+="<thead>";
        html+="<tr>";
        html+="<th id=\"smart_name\" class=\"rightCell\">"+genlang(2, 'smart')+"</th>"; // Name
        for (i = 0; i < smartitems.length ; i++) {
            smartid = smartitems[i]["@attributes"].id;
            html+="<th class=\"sorttable_numeric rightCell\">"+ genlang(100+parseInt(smartid, 10), 'smart', smartid) + "</th>";
        }
        html+="</tr>";
        html+="</thead>";

        var diskitems = items(data.Plugins.Plugin_SMART.disks.disk);
        html += '<tbody>';
        for (i = 0; i < diskitems.length; i++) {
            html += '<tr>';
            if (diskitems[i]["@attributes"].event !== undefined)
                html += '<th class="rightCell"><table class="borderless table-hover table-nopadding" style="float:right;"><tbody><tr><td>'+ diskitems[i]["@attributes"].name + ' </td><td><img style="vertical-align:middle;width:20px;" src="./gfx/attention.gif" alt="!" title="' + diskitems[i]["@attributes"].event + '"/></td></tr></tbody></table></th>';
            else
                html += '<th class="rightCell">'+ diskitems[i]["@attributes"].name + '</th>';
            attribitems = items(diskitems[i].attribute);
            var valarray = [];
            for (j = 0;j < attribitems.length; j++) {
                valarray[attribitems[j]["@attributes"].id] = attribitems[j]["@attributes"];
            }
            for (j = 0; j < smartitems.length; j++) {
                smartid = smartitems[j]["@attributes"].id;
                if ((smartid !== undefined) && (valarray[smartid] !== undefined)) {
                    var itemvalue = valarray[smartid][smartitems[j]["@attributes"].name];
                    if ((itemvalue !== undefined) && (itemvalue !== '' )) {
                        if (smartid === "194") {
                            html += '<td class="rightCell">' + formatTemp(itemvalue, data.Options["@attributes"].tempFormat) + '</td>';
                        } else {
                            html += '<td class="rightCell">' + itemvalue + '</td>';
                        }
                    } else {
                        html += '<td></td>';
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
