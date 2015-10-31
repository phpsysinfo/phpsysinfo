function renderPlugin_dmraid(data) {

    function raid_diskicon(data) {
        var html = "";
        var img = "", alt = "";

        html += "<div style=\"text-align: center; float: left; margin-bottom: 5px; margin-right: 20px; width: 64px;\">";
        switch (data["Status"]) {
            case " ":
            case "":
                img = "harddriveok.png";
                alt = "ok";
                break;
            case "F":
                img = "harddrivefail.png";
                alt = "fail";
                break;
            case "S":
                img = "harddrivespare.png";
                alt = "spare";
                break;
            case "W":
                img = "harddrivewarn.png";
                alt = "warning";
                break;
            default:
                alert("--" + data["Status"] + "--");
                img = "error.png";
                alt = "error";
                break;
        }
        html += "<img src=\"./plugins/dmraid/gfx/" + img + "\" alt=\"" + alt + "\" />";
        html += "<small>" + data["Name"] + "</small>";
        html += "</div>";
        return html;
    }

    if (data['Plugins']['Plugin_DMRaid'] !== undefined) {
        var dmitems = items(data['Plugins']['Plugin_DMRaid']['Raid']);
        if (dmitems.length > 0) {
            var html = '';
            for (i = 0; i < dmitems.length ; i++) {
                if (i) {
                    html += "<tr><td></td><td>";
                } else {
                    html += "<tr><th>RAID-Devices</th><td>";
                }

                if (dmitems[i]['Disks'] !== undefined) {
                    html += "<table style=\"width:100%;\">";
                    html += "<tr><td>";

                    var diskitems = items(dmitems[i]['Disks']['Disk']);
                    for (j = 0; j < diskitems.length ; j++) {
                        html += raid_diskicon(diskitems[j]["@attributes"]);
                    }

                    html += "</td></tr><tr><td>";
                    html += "<table id=\"dmraid-" + i + "\"class=\"table table-hover table-condensed\">";
                    html += "<tr class=\"treegrid-dmraid-" + i + "\"><td><b>" + dmitems[i]["@attributes"]["Device_Name"] + "</b></td><td></td></tr>";
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_004','dmraid')+"</td><td>" + dmitems[i]["@attributes"]["Name"] + "</td></tr>";  		// Name
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_005','dmraid')+"</th><td>" + dmitems[i]["@attributes"]["Disk_Status"] + "</td></tr>"; 	// Status
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_006','dmraid')+"</th><td>" + dmitems[i]["@attributes"]["Type"] + "</td></tr>"; 			// RAID-Type
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_007','dmraid')+"</th><td>" + parseInt(dmitems[i]["@attributes"]["Size"]) + "</td></tr>";// Size
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_008','dmraid')+"</th><td>" + parseInt(dmitems[i]["@attributes"]["Stride"]) + "</td></tr>"; // Stride
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_009','dmraid')+"</th><td>" + parseInt(dmitems[i]["@attributes"]["Subsets"]) + "</td></tr>"; // Subsets
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_010','dmraid')+"</th><td>" + parseInt(dmitems[i]["@attributes"]["Devs"]) + "</td></tr>";  	// Devices
                    html += "<tr class=\"treegrid-parent-dmraid-" + i + "\"><th>"+getTranslationString('plugin_dmraid_011','dmraid')+"</th><td>" + parseInt(dmitems[i]["@attributes"]["Spares"]) + "</td></tr>";	// Spares
                    html += "</table>";
                    html += "</td></tr>";
                    html += "</table>";
                }

                html +="</td></tr>";
            }
            $('#dmraid').empty().append(html);

            for (i = 0; i < dmitems.length ; i++) {
                if (dmitems[i]['Disks'] !== undefined) {
                    $('#dmraid-'+i).treegrid({
                        initialState: 'collapsed',
                        expanderExpandedClass: 'normalicon normalicon-down',
                        expanderCollapsedClass: 'normalicon normalicon-right'
                    });
                }
            }

            $('#block_dmraid').show();
        } else {
            $('#block_dmraid').hide();
        }
    } else {
        $('#block_dmraid').hide();
    }
}
