window.addEventListener("load", function() {
    var $table = $("section#doc-history table");
    var url = "/api/routes/{trackingId}";
    var table = UI.createTable($table, {
        cols:    ["id", "office_name", "status"],
        colNames: {
            "office_name": "office name",
        },
        actions: {
            "x": function (index, data, $tr) {
            },
        }
    });
    $("input.trackingId").change(function() {
        var id = $(this).val();
        table.url = util.interpolate(url, { trackingId: id });
        table.fetchData();
    });

});
