function renderPlugin_uprecords(data) {

    var directives = {
        hash: {
            html: function () {
                return this["hash"];
            }
        }
    };

    if ((data['Plugins']['Plugin_uprecords'] !== undefined) && (data['Plugins']['Plugin_uprecords']['Uprecords'] !== undefined)) {
        var upitems = items(data['Plugins']['Plugin_uprecords']['Uprecords']['Item']);
        if (upitems.length > 0) {
            var up_memory = [];
            up_memory.push_attrs(upitems);
            $('#uprecords-data').render(up_memory, directives);

            $('#block_uprecords').show();
        } else {
            $('#block_uprecords').hide();
        }
    } else {
        $('#block_uprecords').hide();
    }
}
