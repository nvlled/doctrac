var users = {
    setup: function ($container) {
        var $rows = $container.find("tbody");
        var $selPos     = $container.find("select.positions");
        var $selPriv    = $container.find("select.privileges");
        var $selOffices = $container.find("select.offices");

        fetchPositions();
        fetchPrivileges();
        fetchOffices();
        setupAddForm();

        function setupAddForm() {
            var $btn = $container.find("button.add");
            $btn.click(function(e) {
                e.preventDefault();
                var pos = {
                    firstname: $container.find(".firstname").val(),
                    middlename: $container.find(".middlename").val(),
                    lastname: $container.find(".lastname").val(),
                    positionId: 
                        $container.find("select.positions").val(),
                    privilegesId: 
                        $container.find("select.privileges").val(),
                    officeId: 
                        $container.find("select.offices").val(),
                    // TODO:
                };
                api.users.add(pos, function(resp) {
                    // TODO: show error messages
                    addRow(resp);
                    clearInputs();
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
                " <td class='fullname'></td>",
                " <td class='position'></td>",
                " <td class='privilege'></td>",
                " <td class='action'>",
                "   <a href='#' class='del'>X</a>",
                "</td>",
                "</tr>",
            ]);
            $tr.find(".id").text(row.id);
            $tr.find(".fullname").text(row.fullname);
            $tr.find(".position").text(row.position);
            $tr.find(".privilege").text(row.privilege);
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
            api.position.delete(row.id);
        }

        function fetchPositions() {
            api.position.fetch(function(poss) {
                poss.forEach(function(pos) {
                    var $option = $("<option>");
                    $option.val(pos.id);
                    $option.text(pos.name);
                    $selPos.append($option);
                });
            });
        }

        function fetchPrivileges() {
            api.privilege.fetch(function(privs) {
                privs.forEach(function(priv) {
                    var $option = $("<option>");
                    $option.val(priv.id);
                    $option.text(priv.name);
                    $selPriv.append($option);
                });
            });
        }

        function fetchOffices() {
            api.office.fetch(function(offices) {
                offices.forEach(function(off) {
                    var $option = $("<option>");
                    $option.val(off.id);
                    $option.text(off.name + " " + off.campus);
                    $selOffices.append($option);
                });
            });
        }
    }
}
window.addEventListener("load", function() {
    users.setup($("div.users"));
});
