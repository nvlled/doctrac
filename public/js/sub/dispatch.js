
var dispatch = {
    setup: function($container) {
        var $selOffices = $container.find("select.offices");
        var $table = $container.find("table.route");
        var $message = $container.find(".message");

        fetchOffices();
        setupAddButton();
        setupSendButton();

        function setupSendButton() {
            var $btn = $container.find("button.send");
            $btn.click(function() {
                $message.text("");
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
                $btn.text("sending...");
                api.doc.send(doc, function(resp) {
                    $btn.text("Send");
                    if (resp.errors)
                        UI.showErrors($container, resp.errors);
                    else {
                        $message.text("document sent: " + doc.trackingId);
                        console.log("okay", resp);
                        $table.find("tbody").html("");
                        $container.find("form")[0].reset();
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
                $table.append($tr);
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
