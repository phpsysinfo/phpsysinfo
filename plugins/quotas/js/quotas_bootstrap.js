function renderPlugin_Quotas(data) {

    var directives = {

        ByteUsed: {
            text: function () {
                return formatBytes(this["ByteUsed"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        ByteSoft: {
            text: function () {
                return formatBytes(this["ByteSoft"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        ByteHard: {
            text: function () {
                return formatBytes(this["ByteHard"], data["Options"]["@attributes"]["byteFormat"]);
            }
        },
        BytePercentUsed: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width: ' + this["BytePercentUsed"] + '%;"></div>' +
                        '</div><div class="percent">' + this["BytePercentUsed"] + '%</div>';
            }
        },
        FilePercentUsed: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width: ' + this["FilePercentUsed"] + '%;"></div>' +
                        '</div><div class="percent">' + this["FilePercentUsed"] + '%</div>';
            }
        }
    };

    if (data['Plugins']['Plugin_Quotas'] !== undefined) {
        var qtitems = items(data['Plugins']['Plugin_Quotas']['Quota']);
        if (qtitems.length > 0) {
            var qt_memory = [];
            for (i = 0; i < qtitems.length ; i++) {
                qt_memory.push(qtitems[i]["@attributes"]);
            }
            $('#quotas-data').render(qt_memory, directives);
            sorttable.innerSortFunction.apply($('#quotas_User')[0], []);

            $('#block_quotas').show();
        }
    }
}
