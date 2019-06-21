function renderPlugin_stablebit(data) {

    var directives = {
        IsHot: {
            html: function () {
                return (this.IsHot === "1") ? "<span class=\"badge badge-danger\">YES</span>" : "<span class=\"badge badge-success\">NO</span>";
            }
        },
        IsSmartWarning: {
            html: function () {
                return (this.IsSmartWarning === "1") ? "<span class=\"badge badge-danger\">YES</span>" : "<span class=\"badge badge-success\">NO</span>";
            }
        },
        IsSmartPastThresholds: {
            html: function () {
                return (this.IsSmartPastThresholds === "1") ? "<span class=\"badge badge-danger\">YES</span>" : "<span class=\"badge badge-success\">NO</span>";
            }
        },
        IsSmartPastAdvisoryThresholds: {
            html: function () {
                return (this.IsSmartPastAdvisoryThresholds === "1") ? "<span class=\"badge badge-danger\">YES</span>" : "<span class=\"badge badge-success\">NO</span>";
            }
        },
        IsSmartFailurePredicted: {
            html: function () {
                return (this.IsSmartFailurePredicted === "1") ? "<span class=\"badge badge-danger\">YES</span>" : "<span class=\"badge badge-success\">NO</span>";
            }
        },
        IsDamaged: {
            html: function () {
                return (this.IsDamaged === "1") ? "<span class=\"badge badge-danger\">YES</span>" : "<span class=\"badge badge-success\">NO</span>";
            }
        },
        TemperatureC: {
            html: function () {
                return formatTemp(this.TemperatureC, data.Options["@attributes"].tempFormat);
            }
        },
        Size: {
            html: function () {
                return formatBytes(this.Size, data.Options["@attributes"].byteFormat);
            }
        }
    };

    if (data.Plugins.Plugin_StableBit !== undefined) {
        var disks = items(data.Plugins.Plugin_StableBit.Disk);
        if (disks.length > 0) {
            var i, proc_param;
            var html = "";
            var paramlist = {SerialNumber:4, Firmware:5, Size:6, PowerState:7, TemperatureC:8,  IsHot:9, IsSmartWarning:10, IsSmartPastThresholds:11, IsSmartPastAdvisoryThresholds:12, IsSmartFailurePredicted:13, IsDamaged:14};
            for (i = 0; i < disks.length; i++) {
                try {
                    html+="<tr id=\"stablebit-" + i + "\" class=\"treegrid-stablebit-" + i + "\" style=\"display:none;\" >";
                    html+="<td><span class=\"treegrid-spanbold\" data-bind=\"Name\"></span></td>";
                    html+="<td></td>";
                    html+="</tr>";
                    for (proc_param in paramlist) {
                        if (disks[i]["@attributes"][proc_param] !== undefined) {
                            html+="<tr id=\"stablebit-" + i + "-" + proc_param + "\" class=\"treegrid-parent-stablebit-" + i + "\">";
                            html+="<td><span class=\"treegrid-spanbold\">" + genlang(paramlist[proc_param], 'stablebit') + "</span></td>";
                            html+="<td class=\"rightCell\"><span data-bind=\"" + proc_param + "\"></span></td>";
                            html+="</tr>";
                        }
                    }
                }
                catch (err) {
                   $("#stablebit-" + i).hide();
                }
            }

            $("#stablebit-data").empty().append(html);

            for (i = 0; i < disks.length; i++) {
                try {
                    $('#stablebit-'+ i).render(disks[i]["@attributes"]);
                    $("#stablebit-" + i).show();
                    for (proc_param in paramlist) {
                        if (disks[i]["@attributes"][proc_param] !== undefined) {
                            $('#stablebit-'+ i+ "-" + proc_param).render(disks[i]["@attributes"], directives);
                        }
                    }
                }
                catch (err) {
                   $("#stablebit-" + i).hide();
                }
            }

            $('#stablebit').treegrid({
                initialState: 'expanded',
                expanderExpandedClass: 'normalicon normalicon-down',
                expanderCollapsedClass: 'normalicon normalicon-right'
            });

            $('#block_stablebit').show();
        } else {
            $('#block_stablebit').hide();
        }
    } else {
        $('#block_stablebit').hide();
    }
}
