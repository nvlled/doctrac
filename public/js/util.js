
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
        data = data || {};
        return str.replace(/{(\w+)}/g, function(...args) {
            var k = args[1];
            var v = data[k];
            if (v == null)
                return args[0];
            return v;
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

    loadJson: function(sel, promise) {
        var json = $(sel).val();
        try {
            var obj = JSON.parse(json);
            if (obj) {
                return Promise.resolve(obj);
            }
        } catch(e) { }
        return promise;
    },

    refresh: function() {
        window.location.reload();
    },

    redirect: function(url) {
        window.location = url;
    },

    redirectRoute: function(routeName, params) {
        params["routeName"] = routeName;
        api.util.urlFor(params).then(function(resp) {
            if (resp.url) {
                util.redirect(resp.url);
            } else if (resp.errors) {
                console.warn("failed to redirect", resp.errors);
            }
        });
    },

    toArray: function(iterator) {
        var result = [];
        while(true) {
            var state = iterator.next();
            if (!state || state.done) {
                break;
            }
            result.push(state.value);
        }
        return result;
    },

    getFormData: function(form) {
        if (form instanceof $) // is a jquery object
            form = form[0];

        var formData = new FormData(form);
        var result = {};
        util.toArray(formData.entries()).forEach(function(entry) {
            var k = entry[0];
            var v = entry[1];
            result[k] = v;
        });
        return result;
    },

    parseJSON: function(str) {
        try {
            return JSON.parse(str);
        } catch (e) { }
        return null;
    },

    truncate: function(text, limit) {
        if (text.length < limit)
            return text;
        return text.slice(0, limit) + "...";
    }
}
