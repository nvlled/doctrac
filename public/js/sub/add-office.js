
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
            UI.clearMessages($container);
            var data = {
                office:   $container.find(".office-name").val(),
                campus:   $container.find(".campus-name").val(),
            };
            console.log(data);
            api.office.add(data, function(resp) {
                if (resp.errors)
                    UI.showErrors($container, resp.errors);
                else {
                    UI.showMessages($container, "new office added: " + resp.complete_name);
                    addOfficeRow(resp);
                    clearInputs();
                }
            });
        });
        var $btnReset = $container.find("button.reset");
        $btnReset.click(function() {
            if (confirm("Reset all office data?"))
                $btnReset.text("resetting data...");
                $btnReset.attr("disabled", true);
                api.dev.resetOffices().then(function() {
                    location.reload();
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
            " <td class='campus'></td>",
            " <td class='name'></td>",
            " <td class='username'></td>",
            " <td class='action'>",
            "   <a href='#' class='del'>X</a>",
            "</td>",
            "</tr>",
        ]);
        $tr.find(".campus").text(office.campus_name);
        $tr.find(".name").text(office.name);
        $tr.find(".username").text(office.username);
        $tr.find(".del").click(function(e) {
            e.preventDefault();
            var proceed = confirm("delete office: "
                + office.name + "--" + office.campus_name);
            if (!proceed)
                return;
            deleteRow(office, $tr);
        });
        $officeRows.prepend($tr);
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
