
var util = {
    jq: function(lines, attrs) {
        var $elem;
        if (typeof lines.join == "function")
            $elem =  $(lines.join("\n"));
        else
            $elem = $(lines);

        if (attrs) {
            Object.keys(attrs).forEach(function(k) {
                var v = attrs[k];
                if (k == "value")
                    $elem.val(v);
                if (k == "text")
                    $elem.text(v);
                else
                    $elem.attr(k, v);
            });
        }
        return $elem;
    },

    randomStr: function() {
        return Math.random().toString(36).slice(2);
    },

    interpolate: function(str, data) {
        return str.replace(/{(\w+)}/g, function(...args) {
            var k = args[1];
            return data[k] || args[0];
        });
    },

    storeGet: function(k) {
        try {
        return JSON.parse(localStorage[k]);
        } catch (e) { }
        return null;
    },
    storeSet: function(k, v) {
        localStorage[k] = JSON.stringify(v);
    },
}
