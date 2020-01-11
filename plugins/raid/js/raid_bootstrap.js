function renderPlugin_raid(data) {

    function raid_buildaction(data) {
        var html = "", name = "", percent = 0;
        if (data !== undefined) {
            name = data.Name;
            if ((name !== undefined) && (parseInt(name) !== -1)) {
                percent = Math.round(parseFloat(data.Percent));
                html += "<div>" + genlang(11,'raid') + ":" + String.fromCharCode(160) + name + "<br>";
                html += '<table class="table table-nopadding" style="width:100%;"><tbody><tr><td style="width:44%;"><div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width:' + percent + '%;"></div>' +
                        '</div><div class="percent">' + percent + '%</div></td><td>&nbsp;</td></tr></tbody></table>';
                if ((data.Time_To_Finish !== undefined) && (data.Time_Unit !== undefined)) {
                    html += genlang(12,'raid') + ":" + String.fromCharCode(160) + data.Time_To_Finish + String.fromCharCode(160) + data.Time_Unit;
                }
                html += "</div>";
            }
        }
        return html;
    }

    function raid_diskicon(data , id, itemid) {
        var info = "";

        info = data.Info;
        if (info === undefined) info = "";
        parentid = parseInt(data.ParentID, 10);

        var img = "", alt = "", bcolor = "";
        switch (data.Status) {
        case "ok":
            img = "harddriveok.png";
            alt = "ok";
            bcolor = "green";
            break;
        case "F":
            img = "harddrivefail.png";
            alt = "fail";
            bcolor = "red";
            break;
        case "S":
            img = "harddrivespare.png";
            alt = "spare";
            bcolor = "gray";
            break;
       case "U":
            img = "harddriveunc.png";
            alt = "unconfigured";
            bcolor = "purple";
            break;
        case "W":
            img = "harddrivewarn.png";
            alt = "warning";
            bcolor = "orange";
            break;
        default:
//            alert("--" + diskstatus + "--");
            img = "error.png";
            alt = "error";

            break;
        }

        if (!isNaN(parentid)) {
            if (data.Type === "disk") {
                $("#raid_item" + id + "-" + parentid).append("<div style=\"margin-bottom:5px;margin-right:10px;margin-left:10px;float:left;text-align:center\" title=\"" + info + "\"><img src=\"./plugins/raid/gfx/" + img + "\" alt=\"" + alt + "\" style=\"width:60px;height:60px;\" /><br><small>" + data.Name + "</small></div>");
            } else {
                if (parentid === 0) {
                    $("#raid_list-" + id).append("<div id=\"raid_item" + id + "-" + (itemid+1) + "\" style=\"border:solid;border-width:2px;border-radius:5px;border-color:" + bcolor + ";margin:10px;float:left;text-align:center\">" + data.Name + "<br></div>");
                } else {
                    $("#raid_item" + id + "-" + parentid).append("<div id=\"raid_item" + id + "-" + (itemid+1) + "\" style=\"border:solid;border-width:2px;border-radius:5px;border-color:" + bcolor + ";margin:10px;float:left;text-align:center\">" + data.Name + "<br></div>");
                } 
            }
        }
    }

    if (data.Plugins.Plugin_Raid !== undefined) {
        var raiditems = items(data.Plugins.Plugin_Raid.Raid);
        if (raiditems.length > 0) {
            var html = '';
            for (var i = 0; i < raiditems.length ; i++) {
                html += "<tr><th>"+raiditems[i]["@attributes"].Device_Name+"</th><td>";

                if (raiditems[i].RaidItems !== undefined) {
                    html += "<table class=\"table table-nopadding\" style=\"width:100%;\"><tbody>";
                    html += "<tr><td id=\"raid_list-" + i + "\"></td></tr>";

                    if (raiditems[i].Action !== undefined) {
                        var buildedaction = raid_buildaction(raiditems[i].Action['@attributes']);
                        if (buildedaction) {
                            html += "<tr><td>" + buildedaction + "</td></tr>";
                        }
                    }

                    html += "<tr><td>";
                    html += "<table id=\"raid-" + i + "\"class=\"table table-hover table-sm\"><tbody>";
                    html += "<tr class=\"treegrid-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">" + genlang(2, "raid") + "</span></td><td></td></tr>";
                    html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(22, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Program + "</td></tr>"; // Program
                    if (raiditems[i]["@attributes"].Name !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(3, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Name + "</td></tr>"; // Name
                    html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(4, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Status + "</td></tr>"; 	// Status
                    if (raiditems[i]["@attributes"].Level !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(5, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Level + "</td></tr>"; // RAID-Level
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Size))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(6, 'raid')+"</span></td><td>" + formatBytes(parseInt(raiditems[i]["@attributes"].Size), data.Options["@attributes"].byteFormat) + "</td></tr>";// Size
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Stride))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(7, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Stride) + "</td></tr>"; // Stride
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Subsets))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(8, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Subsets) + "</td></tr>"; // Subsets
                    if (raiditems[i]["@attributes"].Devs !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(9, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Devs) + "</td></tr>"; // Devices
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Spares))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(10, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Spares) + "</td></tr>"; // Spares

                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Chunk_Size))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(13, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Chunk_Size) + "K</td></tr>";
                    if (raiditems[i]["@attributes"].Algorithm !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(14, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Algorithm + "</td></tr>";
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Persistend_Superblock))) {
                        if (parseInt(raiditems[i]["@attributes"].Persistend_Superblock) == 1) {
                            html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(15, 'raid')+"</span></td><td>"+genlang(16, 'raid')+"</td></tr>";
                        } else {
                            html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(15, 'raid')+"</span></td><td>"+genlang(17, 'raid')+"</td></tr>";
                        }
                    }
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Disks_Registered)) && !isNaN(parseInt(raiditems[i]["@attributes"].Disks_Active))) {
                        html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(18, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Disks_Registered) + "/<wbr>" + parseInt(raiditems[i]["@attributes"].Disks_Active) + "</td></tr>";
                    }
                    if (raiditems[i]["@attributes"].Controller !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(19, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Controller + "</td></tr>"; // Controller
                    if (raiditems[i]["@attributes"].Battery !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(20, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Battery + "</td></tr>"; // Battery Condition
                    if (raiditems[i]["@attributes"].Supported !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(21, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Supported + "</td></tr>"; // Supported RAID-Types
                    if (raiditems[i]["@attributes"].ReadPolicy !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(23, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].ReadPolicy + "</td></tr>"; // Read Policy
                    if (raiditems[i]["@attributes"].WritePolicy !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(24, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].WritePolicy + "</td></tr>"; // Write Policy
                    if (raiditems[i]["@attributes"].Cache_Size !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(25, 'raid')+"</span></td><td>" + formatBytes(parseInt(raiditems[i]["@attributes"].Cache_Size), data.Options["@attributes"].byteFormat) + "</td></tr>"; // Cache_Size
                    if (raiditems[i]["@attributes"].Bad_Blocks !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(26, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Bad_Blocks) + "</td></tr>"; // Bad_Blocks

                    html += "</tbody></table>";
                    html += "</td></tr>";
                    html += "</tbody></table>";
                }
                /*if (i < raiditems.length-1) { // not last element
                    html += "<br>";
                }*/
                html +="</td></tr>";
            }
            $('#raid-data').empty().append(html);

            for (var k = 0; k < raiditems.length ; k++) {
                if (raiditems[k].RaidItems !== undefined) {
                    var diskitems = items(raiditems[k].RaidItems.Item);
                    for (var j = 0; j < diskitems.length ; j++) {
                        raid_diskicon(diskitems[j]["@attributes"], k, j);
                    }
                    $('#raid-'+k).treegrid({
                        initialState: 'collapsed',
                        expanderExpandedClass: 'normalicon normalicon-down',
                        expanderCollapsedClass: 'normalicon normalicon-right'
                    });
                }
            }

            $('#block_raid').show();
        } else {
            $('#block_raid').hide();
        }
    } else {
        $('#block_raid').hide();
    }
}
