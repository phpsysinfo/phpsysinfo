function renderPlugin_viewer(data) {

    var directives = {
        Line: {
            html: function () {
                if (this.Line === "")
                    return "&nbsp;";
                else
                    return this.Line;
            }
        }
    };

    if ((data.Plugins.Plugin_Viewer !== undefined) && (data.Plugins.Plugin_Viewer.Viewer !== undefined)) {
        var name = data.Plugins.Plugin_Viewer.Viewer["@attributes"].Name;
        $('#viewer-th').empty();
        if (name !== undefined) $('#viewer-th').append(name);

        var upitems = items(data.Plugins.Plugin_Viewer.Viewer.Item);
        if (upitems.length > 0) {
            var up_memory = [];
            up_memory.push_attrs(upitems);
            $('#viewer-data').render(up_memory, directives);

            $('#block_viewer').show();
        } else {
            $('#block_viewer').hide();
        }
    } else {
        $('#block_viewer').hide();
    }
}
