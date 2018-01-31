
var privileges = {
    setup: function ($container) {
        var $rows = $container.find("tbody");

        fetchPositions();
        setupAddForm();

        function setupAddForm() {
            var $btn = $container.find("button.add");
            $btn.click(function(e) {
                e.preventDefault();
                var priv = {
                    name:   $container.find(".priv-name").val(),
                };
                api.privilege.add(priv, function(resp) {
                    addRow(resp);
                    clearInputs();
                });
            });
        }

        function clearInputs() {
            $container.find(".priv-name").val("");
        }

        function addRow(row) {
            var $tr = util.jq([
                "<tr>",
                " <td class='id'></td>",
                " <td class='name'></td>",
                " <td class='action'>",
                "   <a href='#' class='del'>X</a>",
                "</td>",
                "</tr>",
            ]);
            $tr.find(".id").text(row.id);
            $tr.find(".name").text(row.name);
            $tr.find(".del").click(function(e) {
                e.preventDefault();
                var proceed = confirm("delete row: "+row.name );
                if (!proceed)
                    return;
                deleteRow(row, $tr);
            });
            $rows.append($tr);
        }

        function deleteRow(row, $tr) {
            $tr.remove();
            api.privilege.delete(row.id);
        }

        function fetchPositions() {
            api.privilege.fetch(function(privs) {
                if (!privs.forEach) {
                    throw "response for privileges list is not an array";
                }
                privs.forEach(function(priv) {
                    addRow(priv);
                });
            });
        }
    }
}
window.addEventListener("load", function() {
    privileges.setup($("div.privileges"));
});
