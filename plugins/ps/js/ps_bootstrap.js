function renderPlugin_ps(data) {

    var directives = {
        MemoryUsage: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width: ' + this["MemoryUsage"] + '%;"></div>' +
                        '</div><div class="percent">' + this["MemoryUsage"] + '%</div>';
            }
        }
    };

    if (data['Plugins']['Plugin_PS'] !== undefined) {
        var psitems = items(data['Plugins']['Plugin_PS']['Process']);
        if (psitems.length > 0) {
            var ps_memory = [];
            var ps_item = [];
            for (i = 0; i < psitems.length ; i++) {
                ps_item = psitems[i]["@attributes"];
                ps_item["number"] = i + 1;
                ps_memory.push(ps_item);
            }
            $('#ps-data').render(ps_memory, directives);
            $('#ps_number').removeClass("sorttable_sorted"); // reset sort order
            sorttable.innerSortFunction.apply($('#ps_number')[0], []);

            $('#block_ps').show();
        } else {
            $('#block_ps').hide();
        }
    } else {
        $('#block_ps').hide();
    }
}
