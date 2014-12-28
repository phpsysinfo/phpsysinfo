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
        $('#block_uprecords').show();
            var up_memory = [];
            for (i = 0; i < upitems.length ; i++) {
                up_memory.push(upitems[i]["@attributes"]);
            }
            $('#uprecords-data').render(up_memory, directives);

            $('#block_uprecords').show();
        }
    }
}
