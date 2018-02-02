
var dispatch = {
    setup: function($container) {
        var $destinations = $container.find("table");
        var $selOffices = $container.find("select.offices");
        var $table = $container.find("table.route");

        fetchOffices();
        setupAddButton();
        setupSendButton();

        function setupSendButton() {
            var $btn = $container.find("button.send");
            $btn.click(function() {
                UI.clearErrors($container);
                var officeIds = [];
                $table.find("tbody tr").each(function(i) {
                    var id = $(this).data("officeId");
                    officeIds.push(id);
                });
                var doc = {
                    userId: $container.find(".userId").val(),
                    title: $container.find(".title").val(),
                    trackingId: $container.find(".trackingId").val(),
                    details: $container.find(".details").val(),
                    officeIds: officeIds,
                    type: getDispatchType(),
                }
                api.doc.send(doc, function(resp) {
                    if (resp.errors)
                        UI.showErrors($container, resp.errors);
                    else {
                        console.log("okay", resp);
                        $btn.text("document sent");
                        $btn[0].disabled = true;
                    }
                });
            });
        }

        function getDispatchType() {
            return $container.find("input[name=dispatch-type]:checked").val();
        }

        function setupAddButton() {
            var $btn = $container.find("button.add");
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
                $tr.data("officeId", office.id);
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
    dispatch.setup($("section#dispatch"));
});
