
var util = {
    jq: function(lines) {
        return $(lines.join("\n"));
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
