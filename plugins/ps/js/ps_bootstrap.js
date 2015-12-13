function renderPlugin_ps(data) {

    var directives = {
        MemoryUsage: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + this["MemoryUsage"] + '%;"></div>' +
                        '</div><div class="percent">' + this["MemoryUsage"] + '%</div>';
            }
        },
        Name: {
            html: function () {
                return this["Name"];
            }
        }
    };

    if (data['Plugins']['Plugin_PS'] !== undefined) {
        var psitems = items(data['Plugins']['Plugin_PS']['Process']);
        if (psitems.length > 0) {
 
            var html = "", ps_item = [], expanded = 1;
            html+="<thead>";
            html+="<tr>";
            html+="<th>"+genlang(3, false, 'ps')+"</th>";// Command
            html+="<th>"+genlang(4, false, 'ps')+"</th>"; // Process ID
            html+="<th>"+genlang(5, false, 'ps')+"</th>"; // Parent ID
            html+="<th>"+genlang(6, false, 'ps')+"</th>"; // Memory Usage
            html+="</tr>";
            html+="</thead>";

            for (var i = 0; i < psitems.length ; i++) {
                ps_item = psitems[i]["@attributes"];
                
                if (ps_item["ParentID"]==="0") {
                    html+="<tr id=\"ps-" + (i+1) + "\" class=\"treegrid-ps-" + (i+1) + "\" style=\"display:none;\" >";
                } else {
                    html+="<tr id=\"ps-" + (i+1) + "\" class=\"treegrid-ps-" + (i+1) + " treegrid-parent-ps-" + ps_item["ParentID"] + "\" style=\"ddisplay:none;\" >";
                }
                html+="<td><span class=\"treegrid-span\" data-bind=\"Name\"></span></td>";
                html+="<td><span data-bind=\"PID\"></span></td>";
                html+="<td><span data-bind=\"PPID\"></span></td>";
                html+="<td><span data-bind=\"MemoryUsage\"></span></td>";
                html+="</tr>";
            } 

            $("#ps").empty().append(html);
            
            $('#ps').treegrid({
                initialState: 'expanded',
                expanderExpandedClass: 'normalicon normalicon-down',
                expanderCollapsedClass: 'normalicon normalicon-right'
            });

            for (var i = 0; i < psitems.length ; i++) {
                ps_item = psitems[i]["@attributes"];
                $('#ps-'+(i+1)).render(ps_item, directives);
                expanded = ps_item["Expanded"];
                if ((expanded !== undefined) && (expanded === "0")) {
                    $('#ps-'+(i+1)).treegrid('collapse');
                }
            }
            $('#block_ps').show();
        } else {
            $('#block_ps').hide();
        }
    } else {
        $('#block_ps').hide();
    }
}
