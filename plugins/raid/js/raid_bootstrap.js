function renderPlugin_raid(data) {

    function raid_buildaction(data) {
        var html = "", name = "", percent = 0;
        if (data !== undefined) {
            name = data.Name;
            if ((name !== undefined) && (parseInt(name, 10) !== -1)) {
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

    function raid_diskicon(data , id, itemid, byteFormat, tempFormat) {
        var info = "";
        info = data.Info;
        if (info === undefined) info = "";
        parentid = parseInt(data.ParentID, 10);

        var imgh = "", imgs = "", alt = "", bcolor = "", bus = "", cap = "", minfo = "", serial = "";
        switch (data.Status) {
        case "ok":
            imgh = "harddriveok.png";
            imgs = "soliddriveok.png";
            alt = "ok";
            bcolor = "green";
            break;
        case "F":
            imgh = "harddrivefail.png";
            imgs = "soliddrivefail.png";
            alt = "fail";
            bcolor = "red";
            break;
        case "S":
            imgh = "harddrivespare.png";
            imgs = "soliddrivespare.png";
            alt = "spare";
            bcolor = "gray";
            break;
       case "U":
            imgh = "harddriveunc.png";
            imgs = "soliddriveunc.png";
            alt = "unconfigured";
            bcolor = "purple";
            break;
        case "W":
            imgh = "harddrivewarn.png";
            imgs = "soliddrivewarn.png";
            alt = "warning";
            bcolor = "orange";
            break;
        default:
//            alert("--" + diskstatus + "--");
            imgh = "error.png";
            imgs = "error.png";
            alt = "error";

            break;
        }

        if (!isNaN(parentid)) {
            if (data.Type !== undefined) {
                if (data.Model !== undefined) {
                    minfo = "<br>" + data.Model;
                }
                if (data.Serial !== undefined) {
                    minfo += "<br>" + data.Serial;
                }
                if (data.Bus === undefined) {
                    bus = "";
                } else {
                    bus = data.Bus;
                }
                if (!isNaN(parseInt(data.Capacity, 10))) {
                    cap = formatBytes(parseInt(data.Capacity, 10), byteFormat);
                }
                if ((bus !== "") || (cap !== "")) {
                    minfo += "<br>" + $.trim(bus + " " + cap);
                }
                if (isFinite(parseFloat(data.Temperature))) {
                    minfo += "<br>" + formatTemp(parseFloat(data.Temperature), tempFormat);
                }
                $("#raid_item" + id + "-" + parentid).append("<div style=\"margin-bottom:5px;margin-right:10px;margin-left:10px;float:left;text-align:center\" title=\"" + info + "\"><img src=\"./plugins/raid/gfx/" + ((data.Type === "ssd")?imgs:imgh) + "\" alt=\"" + alt + "\" style=\"width:60px;height:60px;\" /><br><small>" + data.Name + minfo + "</small></div>");   
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
                    html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(4, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Status + "</td></tr>"; // Status
                    if (raiditems[i]["@attributes"].Level !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(5, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Level + "</td></tr>"; // RAID-Level
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Capacity, 10))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(6, 'raid')+"</span></td><td>" + formatBytes(parseInt(raiditems[i]["@attributes"].Capacity, 10), data.Options["@attributes"].byteFormat) + "</td></tr>";// Capacity
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Stride, 10))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(7, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Stride, 10) + "</td></tr>"; // Stride
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Subsets, 10))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(8, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Subsets, 10) + "</td></tr>"; // Subsets
                    if (raiditems[i]["@attributes"].Devs !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(9, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Devs, 10) + "</td></tr>"; // Devices
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Spares, 10))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(10, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Spares, 10) + "</td></tr>"; // Spares

                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Chunk_Size, 10))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(13, 'raid')+"</span></td><td>" + formatBytes(1024*parseInt(raiditems[i]["@attributes"].Chunk_Size, 10), data.Options["@attributes"].byteFormat) + "</td></tr>";
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Stripe_Size, 10))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(28, 'raid')+"</span></td><td>" + formatBytes(parseInt(raiditems[i]["@attributes"].Stripe_Size, 10), data.Options["@attributes"].byteFormat) + "</td></tr>";
                    if (raiditems[i]["@attributes"].Algorithm !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(14, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Algorithm + "</td></tr>";
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Persistend_Superblock, 10))) {
                        if (parseInt(raiditems[i]["@attributes"].Persistend_Superblock, 10) == 1) {
                            html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(15, 'raid')+"</span></td><td>"+genlang(16, 'raid')+"</td></tr>";
                        } else {
                            html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(15, 'raid')+"</span></td><td>"+genlang(17, 'raid')+"</td></tr>";
                        }
                    }
                    if (!isNaN(parseInt(raiditems[i]["@attributes"].Disks_Registered, 10)) && !isNaN(parseInt(raiditems[i]["@attributes"].Disks_Active, 10))) {
                        html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(18, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Disks_Registered, 10) + "/<wbr>" + parseInt(raiditems[i]["@attributes"].Disks_Active, 10) + "</td></tr>";
                    }
                    if (raiditems[i]["@attributes"].Controller !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(19, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Controller + "</td></tr>"; // Controller
                    if (raiditems[i]["@attributes"].Firmware !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(29, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Firmware + "</td></tr>"; // Firmware
                    if (isFinite(parseFloat(raiditems[i]["@attributes"].Temperature))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(30, 'raid')+"</span></td><td>" + formatTemp(parseFloat(raiditems[i]["@attributes"].Temperature), data.Options["@attributes"].tempFormat) + "</td></tr>"; // Temperature
                    if (raiditems[i]["@attributes"].Battery !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(20, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Battery + "</td></tr>"; // Battery Condition
                    if (isFinite(parseFloat(raiditems[i]["@attributes"].Batt_Volt))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(31, 'raid')+"</span></td><td>" + round(parseFloat(raiditems[i]["@attributes"].Batt_Volt), 3) + " " + genlang(82) + "</td></tr>"; // Battery Voltage
                    if (isFinite(parseFloat(raiditems[i]["@attributes"].Batt_Temp))) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(32, 'raid')+"</span></td><td>" + formatTemp(parseFloat(raiditems[i]["@attributes"].Batt_Temp), data.Options["@attributes"].tempFormat) + "</td></tr>"; // Battery Temperature                   
                    if (raiditems[i]["@attributes"].Supported !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(21, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].Supported + "</td></tr>"; // Supported RAID-Types
                    if (raiditems[i]["@attributes"].Cache_Size !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(25, 'raid')+"</span></td><td>" + formatBytes(parseInt(raiditems[i]["@attributes"].Cache_Size, 10), data.Options["@attributes"].byteFormat) + "</td></tr>"; // Cache_Size
                    if (raiditems[i]["@attributes"].ReadPolicy !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(23, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].ReadPolicy + "</td></tr>"; // Read Policy
                    if (raiditems[i]["@attributes"].WritePolicy !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(24, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].WritePolicy + "</td></tr>"; // Write Policy
                    if (raiditems[i]["@attributes"].DiskCache !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(27, 'raid')+"</span></td><td>" + raiditems[i]["@attributes"].DiskCache + "</td></tr>"; // Disk Cache
                    if (raiditems[i]["@attributes"].Bad_Blocks !== undefined) html += "<tr class=\"treegrid-parent-raid-" + i + "\"><td><span class=\"treegrid-spanbold\">"+genlang(26, 'raid')+"</span></td><td>" + parseInt(raiditems[i]["@attributes"].Bad_Blocks, 10) + "</td></tr>"; // Bad_Blocks

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
                        raid_diskicon(diskitems[j]["@attributes"], k, j, data.Options["@attributes"].byteFormat, data.Options["@attributes"].tempFormat);
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
