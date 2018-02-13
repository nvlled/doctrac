
var autocomplete = {
    enable: function($input, url) {
        var id = "data-"+($input.attr("id") || util.randomStr());
        var $datalist = $("<datalist>");

        if (url)
            $input.data("url", url || $input.data("url"));
        if (!$input.data("key"))
            $input.data("key", "id");


        var recentData = {};
        var $label = $("<label>");

        $datalist.insertAfter($input);
        $label.insertAfter($input);

        $datalist.attr("id", id);
        $input.attr("list", id);
        $input.attr("autocomplete", "on");

        $input.focus(function() {
            $input.addClass("half");
            $label.hide();
            loadDataList();
        });
        $input.change(function() {
            if (Object.keys(recentData).length == 0) {
                loadDataList().then(update);
            } else {
                update();
            }
        });
        $input.blur(function() {
            $label.show();
            $input.removeClass("half");
        });
        $input.each(function() {
            this.clear = function() {
                $input.val("");
                $input.data("object", null);
                $label.text("");
            }
        });

        $input.keyup(function() {
            loadDataList();
        });

        function update() {
            $input.removeClass("half");
            $label.show();
            var value = $input.val();
            var data = recentData[value];
            var hideText = $input.data("hidetext");

            if (recentData && data) {
                $input.data("object", data.row);
                if (!hideText)
                    $label.text(data.text);
            } else {
                $label.text("");
                $input.data("object", null);
            }
        }
        function loadDataList() {
            var url = $input.data("url");
            var params = $input.data("params") || {};
            params["q"] = $input.val();
            return $.get(url, params)
                .then(function(data) {
                    if (!data || !data.forEach)
                        return;
                    if (data.length == 0)
                        return;
                    $datalist.html("");

                    delete recentData;
                    recentData = {};

                    var key = $input.data("key");
                    data.forEach(function(x) {
                        var format = $input.data("format");
                        var value = x[key];
                        var text = "";
                        if (format) {
                            text = util.interpolate(format, x);
                        } else {
                            text = Object.values(x).toString();
                        }
                        var optionData = {
                            text: text,
                            value: value,
                            row: x,
                        }
                        var $option = util.jq("<option>", optionData);
                        recentData[value] = optionData;
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

