function renderPlugin_quotas(data) {

    var directives = {

        ByteUsed: {
            html: function () {
                return formatBytes(this.ByteUsed, data.Options["@attributes"].byteFormat);
            }
        },
        ByteSoft: {
            html: function () {
                return formatBytes(this.ByteSoft, data.Options["@attributes"].byteFormat);
            }
        },
        ByteHard: {
            html: function () {
                return formatBytes(this.ByteHard, data.Options["@attributes"].byteFormat);
            }
        },
        BytePercentUsed: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + this.BytePercentUsed + '%;"></div>' +
                        '</div><div class="percent">' + this.BytePercentUsed + '%</div>';
            }
        },
        FilePercentUsed: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + this.FilePercentUsed + '%;"></div>' +
                        '</div><div class="percent">' + this.FilePercentUsed + '%</div>';
            }
        }
    };

    if (data.Plugins.Plugin_Quotas !== undefined) {
        var qtitems = items(data.Plugins.Plugin_Quotas.Quota);
        if (qtitems.length > 0) {
            var qt_memory = [];
            qt_memory.push_attrs(qtitems);
            $('#quotas-data').render(qt_memory, directives);
            $('#quotas_User').removeClass("sorttable_sorted"); // reset sort order
            sorttable.innerSortFunction.apply($('#quotas_User')[0], []);

            $('#block_quotas').show();
        } else {
            $('#block_quotas').hide();
        }
    } else {
        $('#block_quotas').hide();
    }
}
