function renderPlugin_docker(data) {

    var directives = {
        CPUUsage: {
            html: function () {
                return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width:' + round(this.CPUUsage,2) + '%;"></div>' +
                        '</div><div class="percent">' + round(this.CPUUsage,2) + '%</div>';
            }
        },
        MemoryUsage: {
            html: function () {
                return '<div class="progress">' +
                        '<div class="progress-bar progress-bar-info" style="width:' + round(this.MemoryUsage,2) + '%;"></div>' +
                        '</div><div class="percent">' + round(this.MemoryUsage,2) + '%</div>';
            }
        },
        MemoryUsed: {
            html: function () {
                return formatBytes(this.MemoryUsed, data.Options["@attributes"].byteFormat);
            }
        },
        MemoryLimit: {
            html: function () {
                return formatBytes(this.MemoryLimit, data.Options["@attributes"].byteFormat);
            }
        }
    };

    if ((data.Plugins.Plugin_Docker !== undefined) && (data.Plugins.Plugin_Docker.Docker !== undefined)) {
        var doitems = items(data.Plugins.Plugin_Docker.Docker.Item);
        if (doitems.length > 0) {
            var do_memory = [];
            do_memory.push_attrs(doitems);
            $('#docker-data').render(do_memory, directives);
            $('#docker_Name').removeClass("sorttable_sorted"); // reset sort order
            sorttable.innerSortFunction.apply($('#docker_Name')[0], []);

            $('#block_docker').show();
        } else {
            $('#block_docker').hide();
        }
    } else {
        $('#block_docker').hide();
    }
}
