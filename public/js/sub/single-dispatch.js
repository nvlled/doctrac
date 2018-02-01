
var singleDispatch = {
    setup: function($container) {
        var $destinations = $container.find("table");
        var $selOffices = $container.find("select.offices");

        fetchOffices();
        setupDestAdd();

        function setupDestAdd() {
            var $btn = $container.find("button");
            $btn.click(function() {
                var $option = $($selOffices[0].selectedOptions[0]);
                if ($option.length == 0)
                    return;

                var office = $option.data("office");
                $option.detach();
                var $tr = util.jq([
                    "<tr>",
                    " <td class='id'></td>",
                    " <td class='name'></td>",
                    " <td class='action'>",
                    "   <a href='#' class='del'>X</a>",
                    "</td>",
                    "</tr>",
                ]);
                $tr.find(".id").text(office.id);
                $tr.find(".name").text(office.campus + " " + office.name);
                $tr.find(".del").click(function(e) {
                    e.preventDefault();
                    $tr.remove();
                    $selOffices.append($option);
                });
                $destinations.append($tr);
            })
        }

        function fetchOffices() {
            api.office.fetch(function(offices) {
                offices.forEach(function(off) {
                    var $option = $("<option>");
                    $option.val(off.id);
                    $option.text(off.name + " " + off.campus);
                    $option.data("office", off);
                    $selOffices.append($option);
                });
            });
        }
    }
}

window.addEventListener("load", function() {
    singleDispatch.setup($("section#single-dispatch"));
});

