
window.addEventListener("load", function() {
    var $container = $("div.add-office");
    var $officeRows = $container.find("tbody");

    fetchOffices();
    setupAddForm();

    function setupAddForm() {
        var $btn = $container.find("button.add");
        $btn.click(function(e) {
            e.preventDefault();
            UI.clearErrors($container);
            var office = {
                name:   $container.find(".office-name").val(),
                campus: $container.find(".campus-name").val(),
            };
            api.office.add(office, function(resp) {
                if (resp.errors)
                    UI.showErrors($container, resp.errors);
                else {
                    addOfficeRow(resp);
                    clearInputs();
                }
            });
        });
    }

    function clearInputs() {
        $container.find(".office-name").val("");
        $container.find(".campus-name").val("");
    }

    function addOfficeRow(office) {
        var $tr = util.jq([
            "<tr>",
            " <td class='id'></td>",
            " <td class='campus'></td>",
            " <td class='name'></td>",
            " <td class='action'>",
            "   <a href='#' class='del'>X</a>",
            "</td>",
            "</tr>",
        ]);
        $tr.find(".id").text(office.id);
        $tr.find(".name").text(office.name);
        $tr.find(".campus").text(office.campus);
        $tr.find(".del").click(function(e) {
            e.preventDefault();
            var proceed = confirm("delete office: " 
                + office.name + "--" + office.campus); 
            if (!proceed)
                return;
            deleteRow(office, $tr);
        });
        $officeRows.append($tr);
    }

    function deleteRow(office, $tr) {
        $tr.remove();
        api.office.delete(office.id, function(resp) {;
            UI.showErrors($container, resp.errors);
        });
    }

    function fetchOffices() {
        api.office.fetch(function(offices) {
            if (!offices.forEach) {
                throw "response for office list is not an array";
            }
            offices.forEach(function(off) {
                addOfficeRow(off);
            });
        });
    }
});
