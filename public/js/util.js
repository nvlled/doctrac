
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

    jqText: function($node, texts) {
        Object.keys(texts).forEach(function(sel) {
            $node.find(sel).text(texts[sel]);
        });
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

    arrayContains: function(array, x) {
        if (!array)
            return false;
        return array.indexOf(x) >= 0;
    },

    arrayAddNew: function(array, x) {
        var i = array.indexOf(x);
        if (i < 0) {
            array.push(x);
        }
        return array;
    },

    arrayRemove: function(array, x) {
        var i = array.indexOf(x);
        if (i >= 0) {
            array.splice(i, 1);
        }
        return array;
    },
}
