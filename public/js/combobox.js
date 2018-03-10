
var combobox = {
    getObject: function($select) {
        var obj = null;
        $select.find("option").each(function() {
            $option = $(this);
            if ($option.val() == $select.val())
                obj = $option.data("object");
        });
        return obj;
    },

    load: function($select, args) {
        args = args || {};
        var url = args.url || $select.data("url") || "";
        var params = args.params || $select.data("params");
        var method = args.method || $select.data("method") || "GET";

        if (params)
            url = util.interpolate(url, params);

        return $.ajax({
            method: method,
            url: url,
        }).then(function(result) {
            this.loadData($select, result);
        }.bind(this));

    },

    loadData: function($select, data) {
        $select.html("");
        if (data.length > 0) {
            var format = $select.data("format");
            var index = $select.data("index") || "id";
            data.forEach(function(obj) {
                var $option = $("<option>");
                var value = obj[index];
                if (format) {
                    $option.text(util.interpolate(format, obj));
                } else {
                    $option.text(value);
                }
                $option.data("object", obj);
                $option.val(value);
                $select.append($option);
            });
        }
    },

    init: function() {
        var self = this;
        $("select.combobox").each(function() {
            self.load($(this));
        });
    },
}

$(function() {
    combobox.init();
});
