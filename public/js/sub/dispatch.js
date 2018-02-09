
var dispatch = {
    setup: function($container) {
        var $selOffices = $container.find("select.offices");
        var $table = $container.find("table.route");
        var $message = $container.find(".message");
        var $btnRand = $container.find("button.rand");
        var $btnSend = $container.find("button.send");
        var $input = $container.find(".trackingId");
        var $userName = $container.find(".user-name");
        var $userOffice = $container.find(".user-office");

        var currentUser = null;
        api.user.change(setCurrentUser);
        api.user.self()
            .then(setCurrentUser);

        $btnRand.click(function(e) {
            e.preventDefault();
            //var user = api.user.self();
            var user = currentUser;
            var officeId = user ? user.officeId : "";
            api.doc.randomId({
                officeId: officeId,
            }, function(id) {
                $input.val(id);
            });
        });

        fetchOffices();
        setupAddButton();
        setupSendButton();

        function setCurrentUser(user) {
            currentUser = user;
            if (user) {
                $userName.text(user.firstname + " " + user.lastname);
                $userOffice.text(user.office_name);
                disableOffice(user.officeId);
                $btnSend.attr("disabled", false);
            } else {
                $userName.text("");
                $userOffice.text("");
                $btnSend.attr("disabled", true);
            }
        }

        function disableOffice(id) {
            var officeId = -1;
            $selOffices.find("option").each(function(_, opt) {
                opt.disabled = false;
                var offId = parseInt(opt.value);
                if (offId == id) {
                    opt.disabled = true;
                    officeId = offId;
                }
            });
            var sel = $selOffices[0];
            if (isSelected(sel, officeId))
                sel.selectedIndex++;
            if (sel.selectedIndex < 0)
                sel.selectedIndex = 0;
        }
        function isSelected(sel, value) {
            if (!sel)
                return false;
            var i = sel.selectedIndex;
            var opt = sel.children[i];
            return opt.value+"" == value+"";
        }

        function setupSendButton() {
            $btnSend.click(function() {
                $message.text("");
                UI.clearErrors($container);
                var officeIds = [];
                $table.find("tbody tr").each(function(i) {
                    var id = $(this).data("officeId");
                    officeIds.push(id);
                });
                var doc = {
                    userId: currentUser ? currentUser.id : null,
                    title: $container.find(".title").val(),
                    trackingId: $container.find(".trackingId").val(),
                    details: $container.find(".details").val(),
                    officeIds: officeIds,
                    type: getDispatchType(),
                }
                $btnSend.text("sending...");
                api.doc.send(doc, function(resp) {
                    $btnSend.text("Send");
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
