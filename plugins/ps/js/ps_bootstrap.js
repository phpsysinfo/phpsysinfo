function renderPlugin_ps(data) {

    var directives = {
        MemoryUsage: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + this.MemoryUsage + '%;"></div>' +
                        '</div><div class="percent">' + this.MemoryUsage + '%</div>';
            }
        },
        CPUUsage: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + this.CPUUsage + '%;"></div>' +
                        '</div><div class="percent">' + this.CPUUsage + '%</div>';
            }
        },
        Name: {
            html: function () {
                return this.Name.replace(/,/g, ",<wbr>").replace(/\s/g, " <wbr>").replace(/\./g, ".<wbr>").replace(/-/g, "<wbr>-").replace(/\//g, "<wbr>/"); /* split long name */
            }
        }
    };

    if (data.Plugins.Plugin_PS !== undefined) {
        var psitems = items(data.Plugins.Plugin_PS.Process);
        if (psitems.length > 0) {

            var html = "", ps_item = [], expanded = 0, memwas = false, cpuwas = false;
            for (var i = 0; i < psitems.length ; i++) {
                ps_item = psitems[i]["@attributes"];

                if (ps_item.ParentID === "0") {
                    html+="<tr id=\"ps-" + (i+1) + "\" class=\"treegrid-ps-" + (i+1) + "\" style=\"display:none;\" >";
                } else {
                    html+="<tr id=\"ps-" + (i+1) + "\" class=\"treegrid-ps-" + (i+1) + " treegrid-parent-ps-" + ps_item.ParentID + "\" style=\"display:none;\" >";
                }
                html+="<td><span class=\"treegrid-span\" data-bind=\"Name\"></span></td>";
                html+="<td><span data-bind=\"PID\"></span></td>";
                html+="<td><span data-bind=\"PPID\"></span></td>";
                html+="<td style=\"width:10%;\"><span data-bind=\"MemoryUsage\"></span></td>";
                html+="<td style=\"width:10%;\"><span data-bind=\"CPUUsage\"></span></td>";
                html+="</tr>";
            }

            $("#ps-data").empty().append(html);

            $('#ps').treegrid({
                initialState: 'expanded',
                expanderExpandedClass: 'normalicon normalicon-down',
                expanderCollapsedClass: 'normalicon normalicon-right'
            });

            for (var j = 0; j < psitems.length ; j++) {
                ps_item = psitems[j]["@attributes"];
                $('#ps-'+(j+1)).render(ps_item, directives);
                if (!memwas && (ps_item.MemoryUsage !== undefined)) {
                    memwas = true;
                }
                if (!cpuwas && (ps_item.CPUUsage !== undefined)) {
                    cpuwas = true;
                }
                expanded = ps_item.Expanded;
                if ((expanded !== undefined) && (expanded === "0")) {
                    $('#ps-'+(j+1)).treegrid('collapse');
                }
            }

            if (memwas) {
                $('#ps td:nth-child(4),#ps th:nth-child(4)').show();
            } else {
                $('#ps td:nth-child(4),#ps th:nth-child(4)').hide();
            }
            if (cpuwas) {
                $('#ps td:nth-child(5),#ps th:nth-child(5)').show();
            } else {
                $('#ps td:nth-child(5),#ps th:nth-child(5)').hide();
            }

            $('#block_ps').show();
        } else {
            $('#block_ps').hide();
        }
    } else {
        $('#block_ps').hide();
    }
}
