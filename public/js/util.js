
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
}
