function renderPlugin_snmppinfo(data) {

    var directives = {
        Device: {
            text: function () {
                var Name = (this["Name"] !== undefined) ? (' (' + this["Name"] + ')'): '';
                return this["Device"] + Name;
            }
        },
        Percent: {
            html: function () {
                var max = parseInt(this["MaxCapacity"]);
                var level = parseInt(this["Level"]);
                var percent = 0;

                if (max>0 && (level>=0) && (level<=max) ) {
                    percent = Math.round(100*level/max);
                } else if (max==-2 && (level>=0) && (level<=100) ) {
                    percent = level;
                } else if (level==-3) {
                    percent = 100;
                }
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width: ' + percent + '%;"></div>' +
                        '</div><div class="percent">' + percent + '%</div>';
            }
        },
        Units: {
            text: function () {
                var max = parseInt(this["MaxCapacity"]);
                var level = parseInt(this["Level"]);

                if (max>0 && (level>=0) && (level<=max) ) {
                    return level+" / "+max;
                } else if (max==-2 && (level>=0) && (level<=100) ) {
                    return level+" / 100";
                } else if (level==-3) {
                    return getTranslationString('plugin_snmppinfo_006','snmpinfo');		// "enough"
                } else {
                    return getTranslationString('plugin_snmppinfo_007','snmpinfo')		// "unknown"
                }
            }
        }
    };

    if (data['Plugins']['Plugin_SNMPPInfo'] !== undefined) {
        var printers = items(data['Plugins']['Plugin_SNMPPInfo']['Printer']);
        if (printers.length > 0) {
            var html = "";
            html+="<thead>";
            html+="<tr>";
            html+="<th>"+getTranslationString('plugin_snmpinfo_003','snmppinfo')+"</th>"; 						// Printer
            html+="<th>"+getTranslationString('plugin_snmpinfo_004','snmppinfo')+"</th>";						// Percent
            html+="<th class=\"rightCell\">"+getTranslationString('plugin_snmppinfo_005','snmpinfo')+"</th>";	// Units
            html+="</tr>";
            html+="</thead>";
            for (var i = 0; i < printers.length; i++) {
                html+="<tr id=\"snmppinfo-" + i + "\" class=\"treegrid-snmppinfo-" + i + "\" style=\"display:none\" >";
                html+="<td><b><span data-bind=\"Device\"></span></b></td>";
                html+="<td></td>";
                html+="<td></td>";
                html+="</tr>";

                try {
                    var datas = items(printers[i]["MarkerSupplies"]);
                    for (var j = 0; j < datas.length; j++) {
                        html+="<tr id=\"snmppinfo-" + i + "-" + j +"\" class=\"treegrid-parent-snmppinfo-" + i + "\">";
                        html+="<th><span data-bind=\"Description\"></span></th>";
                        html+="<td><span data-bind=\"Percent\"></span></td>";
                        html+="<td class=\"rightCell\"><span data-bind=\"Units\"></span></td>";
                        html+="</tr>";
                   }
                }
                catch (err) {
                   $("#snmppinfo-" + i).hide();
                }     
            }
            
            $("#snmppinfo").empty().append(html);

            for (var i = 0; i < printers.length; i++) {
                $('#snmppinfo-'+ i).render(printers[i]["@attributes"], directives);
                try {
                    var datas = items(printers[i]["MarkerSupplies"]);
                    for (var j = 0; j < datas.length; j++) {
                        $('#snmppinfo-'+ i+ "-" + j).render(datas[j]["@attributes"], directives);
                   }
                }
                catch (err) {
                   $("#snmppinfo-" + i).hide();
                }     
            }
            
            $('#snmppinfo').treegrid({
                initialState: 'expanded',
                expanderExpandedClass: 'normalicon normalicon-down',
                expanderCollapsedClass: 'normalicon normalicon-right'
            });

            $('#block_snmppinfo').show();
        } else {
            $('#block_snmppinfo').hide();
        }
    } else {
        $('#block_snmppinfo').hide();
    }
}
