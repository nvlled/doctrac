
var localSave = {
    input: function($input) {
        var id = $input.attr("id");
        if (!id) {
            console.warn("cannot save input value, no id attribute");
            return;
        }
        $input.val(util.storeGet(id));
        $input.change(function() {
            util.storeSet(id, this.value);
        });
        $input.change();
    },

    init: function() {
        $("input.local-save").each(function() {
            localSave.input($(this));
        });
    },
}

$(function() {
    localSave.init();
});
