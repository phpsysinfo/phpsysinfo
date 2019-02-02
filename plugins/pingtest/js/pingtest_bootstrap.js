function renderPlugin_pingtest(data) {

    var directives = {
        PingTime: {
            html: function () { 
                return ((this.PingTime === "lost") ? genlang(4, 'pingtest') : this.PingTime + String.fromCharCode(160) + "ms"); 
            }
        }
    };

    if (data.Plugins.Plugin_PingTest !== undefined) {
        var psitems = items(data.Plugins.Plugin_PingTest.Ping);
        if (psitems.length > 0) {
            var pt_memory = [];
            pt_memory.push_attrs(psitems);
            $('#pingtest-data').render(pt_memory, directives);
            $('#pingtest_Address').removeClass("sorttable_sorted"); // reset sort order
            sorttable.innerSortFunction.apply($('#pingtest_Address')[0], []);

            $('#block_pingtest').show();
        } else {
            $('#block_pingtest').hide();
        }
    } else {
        $('#block_pingtest').hide();
    }
}
