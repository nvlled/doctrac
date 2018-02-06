var positions = {
    setup: function ($container) {
        var $rows = $container.find("tbody");

        fetchPositions();
        setupAddForm();

        function setupAddForm() {
            var $btn = $container.find("button.add");
            $btn.click(function(e) {
                e.preventDefault();
                UI.clearErrors($container);
                var pos = {
                    name:   $container.find(".pos-name").val(),
                };
                api.position.add(pos, function(resp) {
                    if (resp.errors)
                        UI.showErrors($container, resp.errors);
                    else {
                        addRow(resp);
                        clearInputs();
                    }
                });
            });
        }

        function clearInputs() {
            $container.find(".pos-name").val("");
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
            api.position.delete(row.id, function(resp) {;
                UI.showErrors($container, resp.errors);
            });
        }

        function fetchPositions() {
            api.position.fetch(function(poss) {
                if (!poss.forEach) {
                    throw "response for position list is not an array";
                }
                poss.forEach(function(pos) {
                    addRow(pos);
                });
            });
        }
    }
}
window.addEventListener("load", function() {
    positions.setup($("div.positions"));
});
