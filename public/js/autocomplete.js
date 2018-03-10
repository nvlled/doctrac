
var autocomplete = {
    enable: function($input, url) {
        var id = "data-"+($input.attr("id") || util.randomStr());
        var $datalist = $("<datalist>");

        if (url)
            $input.data("url", url || $input.data("url"));
        if (!$input.data("key"))
            $input.data("key", "id");

        var recentData = {};

        $datalist.insertAfter($input);

        $datalist.attr("id", id);
        $input.attr("list", id);
        $input.attr("autocomplete", "on");

        $input.on("set-value", function(_, data) {
            if (data) {
                $input.val(makeValue(data));
            }
        });
        $input.focus(function() {
            $input.select();
            loadDataList();
        });
        // TODO: handle slow network
        $input.change(function() {
            loadDataList().then(update);
        });
        $input.each(function() {
            this.clear = function() {
                $input.val("");
                $input.data("object", null);
            }
        });

        $input.keyup(function() {
            loadDataList();
        });

        function update() {
            var value = $input.val();
            var data = recentData[value];

            if (recentData && data) {
                $input.data("value", data.value);
                $input.data("object", data.row);
                $input.trigger("complete", data.value);
            } else {
                $input.data("value", null);
                $input.data("object", null);
                $input.trigger("complete", null);
            }
        }

        function makeValue(data) {
            var key = $input.data("key");
            var format = $input.data("format");
            var value = data[key];
            var text = "";
            if (format) {
                text = util.interpolate(format, data);
                return "("+value+") "+text;
            }
            return value;
        }

        function loadDataList() {
            var url = $input.data("url");
            var params = $input.data("params") || {};
            params["q"] = $input.val();
            url = util.interpolate(url, params);
            return $.get(url, params)
                .then(function(data) {
                    if (!data || !data.forEach)
                        return;
                    if (data.length == 0)
                        return;
                    $datalist.html("");

                    delete recentData;
                    recentData = {};

                    data.forEach(function(x) {
                        var key = $input.data("key");
                        var value = x[key];
                        value = makeValue(x);

                        var $option = util.jq("<option>", {
                            text: value,
                            row: x,
                        });

                        recentData[value] = {
                            value: x[key],
                            text: value,
                            row: x,
                        };
                        $datalist.append($option);
                    });
                });
        }
    },

    init: function() {
        var self = this;
        $("input.autocomplete").each(function() {
            self.enable($(this));
        });
    },
}

$(function() {
    autocomplete.init();
});
