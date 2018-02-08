
var autocomplete = {
    enable: function($input, url) {
        var id = "data-"+($input.attr("id") || util.randomStr());
        var $datalist = $("<datalist>");

        if (url)
            $input.data("url", url || $input.data("url"));
        if (!$input.data("key"))
            $input.data("key", "id");

        $datalist.insertAfter($input);
        $datalist.attr("id", id);
        $input.attr("list", id);
        $input.attr("autocomplete", "on");

        $input.keyup(function() {
            var url = $input.data("url");
            console.log(">", $input.val(), url);
            $.get(url, {q: $input.val()})
             .then(function(data) {
                 if (!data || !data.forEach)
                     return;
                 if (data.length == 0)
                     return;
                 $datalist.html("");
                 data.forEach(function(x) {
                     var key = $input.data("key");
                     var format = $input.data("format");
                     var value = x[key];
                     var text = "";
                     if (format) {
                         text = util.interpolate(format, x);
                     } else {
                         text = Object.values(x).toString();
                     }
                     var $option = util.jq("<option>", {
                         text: text,
                         value: value,
                     });
                     console.log("data", key, text, value, $option);
                     $datalist.append($option);
                 });
             });
        });
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
