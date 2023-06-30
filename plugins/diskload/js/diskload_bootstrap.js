function renderPlugin_diskload(data) {

    var directives = {
        Load: {
            html: function () {
                return '<div class="progress"><div class="progress-bar progress-bar-info" style="width:' + this.Load + '%;"></div>' +
                        '</div><div class="percent">' + this.Load + '%</div>';
            }
        }
    };

    if (data.Plugins.Plugin_DiskLoad !== undefined) {
        var disks = items(data.Plugins.Plugin_DiskLoad.Disk);
        if (disks.length > 0) {
            var do_disks = [];
            do_disks.push_attrs(disks);
            $('#diskload-data').render(do_disks, directives);
            //$('#diskload_Name').removeClass("sorttable_sorted"); // reset sort order
            //sorttable.innerSortFunction.apply($('#diskload_Name')[0], []);

            $('#block_diskload').show();
        } else {
            $('#block_diskload').hide();
        }
    } else {
        $('#block_diskload').hide();
    }
}
