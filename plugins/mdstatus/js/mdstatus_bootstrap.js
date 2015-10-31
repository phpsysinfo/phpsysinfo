function renderPlugin_mdstatus(data) {

    function raid_buildaction(data) {
        var html = "", name = "", percent = 0;
        if (data !== undefined) {
            name = data['Name'];
            if ((name !== undefined) && (parseInt(name) !== -1)) {
                percent = Math.round(parseFloat(data['Percent']));
                html += "<div>Current Action:" + String.fromCharCode(160) + name + "<br/>";
                html += '<table width=100%><tr><td width=50%><div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width: ' + percent + '%;"></div>' +
                        '</div><div class="percent">' + percent + '%</div></td><td></td></tr></table>';
                html += getTranslationString('plugin_mdstatus_014','mdstatus')+ String.fromCharCode(160) + data['Time_To_Finish'] + String.fromCharCode(160) + data['Time_Unit'];
                html += "</div>";
            }
        }
        return html;
    }

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

    if (data['Plugins']['Plugin_MDStatus'] !== undefined) {
        $('#mdstatus').empty();
        if (data['Plugins']['Plugin_MDStatus']['Supported_Types'] !== undefined) {
            var stitems = items(data['Plugins']['Plugin_MDStatus']['Supported_Types']['Type']);
            if (stitems.length > 0) {
                var htmltypes = "<tr><th>"+getTranslationString('plugin_mdstatus_002','mdstatus')+"</th><th>";
                for (i = 0; i < stitems.length ; i++) {
                    htmltypes += stitems[i]["@attributes"]["Name"] + " ";
                }
                htmltypes += "</th><tr>";
                $('#mdstatus').append(htmltypes);
                $('#block_mdstatus').show();
            } else {
                $('#block_mdstatus').hide();
            }
        } else {
            $('#block_mdstatus').hide();
        }
        var mditems = items(data['Plugins']['Plugin_MDStatus']['Raid']);
        if (mditems.length > 0) {
            var html = '';
            for (i = 0; i < mditems.length ; i++) {
                if (i) {
                    html += "<tr><td></td><td>";
                } else {
                    html += "<tr><th>RAID-Devices</th><td>";
                }

                if (mditems[i]['Disks'] !== undefined) {
                    var devchunk = parseInt(mditems[i]["@attributes"]["Chunk_Size"]);
                    var devsuper = parseInt(mditems[i]["@attributes"]["Persistent_Superblock"]);
                    var devalgo = parseInt(mditems[i]["@attributes"]["Algorithm"]);
                    var devactive = parseInt(mditems[i]["@attributes"]["Disks_Active"]);
                    var devregis = parseInt(mditems[i]["@attributes"]["Disks_Registered"]);

                    html += "<table style=\"width:100%;\">";
                    html += "<tr><td>";

                    var diskitems = items(mditems[i]['Disks']['Disk']);
                    for (j = 0; j < diskitems.length ; j++) {
                        html += raid_diskicon(diskitems[j]["@attributes"]);
                    }

                    html += "</td></tr><tr><td>";
                    if (mditems[i]['Action'] !== undefined) {
                        var buildedaction = raid_buildaction(mditems[i]['Action']['@attributes']);
                        if (buildedaction) {
                            html += "<tr><td>" + buildedaction + "</td></tr>";
                        }
                    }

                    html += "<table id=\"mdstatus-" + i + "\"class=\"table table-hover table-condensed\">";
                    html += "<tr class=\"treegrid-mdstatus-" + i + "\"><td><b>" + mditems[i]["@attributes"]["Device_Name"] + "</b></td><td></td></tr>";
                    html += "<tr class=\"treegrid-parent-mdstatus-" + i + "\"><th>"+getTranslationString('plugin_mdstatus_005','mdstatus')+"</th><td>" + mditems[i]["@attributes"]["Disk_Status"] + "</td></tr>";
                    html += "<tr class=\"treegrid-parent-mdstatus-" + i + "\"><th>"+getTranslationString('plugin_mdstatus_006','mdstatus')+"</th><td>" + mditems[i]["@attributes"]["Level"] + "</td></tr>";
                    if (devchunk !== -1) {
                        html += "<tr class=\"treegrid-parent-mdstatus-" + i + "\"><th>"+getTranslationString('plugin_mdstatus_007','mdstatus')+"</th><td>" + devchunk + "K</td></tr>";
                    }
                    if (devalgo !== -1) {
                        html += "<tr class=\"treegrid-parent-mdstatus-" + i + "\"><th>"+getTranslationString('plugin_mdstatus_008','mdstatus')+"</th><td>" + devalgo + "</td></tr>";
                    }
                    if (devsuper !== -1) {
                        html += "<tr class=\"treegrid-parent-mdstatus-" + i + "\"><th>"+getTranslationString('plugin_mdstatus_009','mdstatus')+"</th><td>available</td></tr>";
                    } else {
                        html += "<tr class=\"treegrid-parent-mdstatus-" + i + "\"><th>"+getTranslationString('plugin_mdstatus_009','mdstatus')+"</th><td>not available</td></tr>";
                    }
                    if (devactive !== -1 && devregis !== -1) {
                        html += "<tr class=\"treegrid-parent-mdstatus-" + i + "\"><th>"+getTranslationString('plugin_mdstatus_012','mdstatus')+"</th><td>" + devregis + "/" + String.fromCharCode(8203)  + devactive + "</td></tr>";
                    }
                    html += "</table>";
                    html += "</td></tr>";
                    html += "</table>";
                }

                html +="</td></tr>";
            }
            $('#mdstatus').append(html);

            for (i = 0; i < mditems.length ; i++) {
                if (mditems[i]['Disks'] !== undefined) {
                    $('#mdstatus-'+i).treegrid({
                        initialState: 'collapsed',
                        expanderExpandedClass: 'normalicon normalicon-down',
                        expanderCollapsedClass: 'normalicon normalicon-right'
                    });
                }
            }
        }
    } else {
        $('#block_mdstatus').hide();
    }
}
