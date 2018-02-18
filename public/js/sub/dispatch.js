
var dispatch = {
    setup: function($container) {
        var $table = $container.find("table.route");
        var $message = $container.find(".message");
        var $btnRand = $container.find("button.rand");
        var $btnSend = $container.find("button.send");
        var $input = $container.find(".trackingId");
        var $userName = $container.find(".user-name");
        var $userOffice = $container.find(".user-office");
        var $addError = $container.find(".add-error");
        var $officeInput = $container.find("#dispatch-officeId");

        var officeIdFilter = [];
        var currentUser = null;
        api.user.change(setCurrentUser);
        api.user.self()
            .then(setCurrentUser);

        $btnRand.click(function(e) {
            e.preventDefault();
            var user = currentUser;
            var officeId = user ? user.officeId : "";
            api.doc.randomId({
                officeId: officeId,
            }, function(id) {
                $input.val(id);
            });
        });

        setupAddButton();
        setupSendButton();

        function setCurrentUser(user) {
            currentUser = user;
            if (user) {
                $userName.text(user.firstname + " " + user.lastname);
                $userOffice.text(user.office_name);

                $officeInput.data("params", {
                    except: officeIdFilter.concat([user.id]),
                });
            } else {
                $userName.text("");
                $userOffice.text("");
            }
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
                        officeIds.splice(0);
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

            $officeInput.on("complete", function() {
                setTimeout(function() {
                    $addError.text("");

                    if (!$officeInput.val())
                        return;

                    var office = $officeInput.data("object");

                    if (!office) {
                        $addError.text("office not found");
                        return;
                    }
                    $officeInput[0].clear();

                    var $tr = util.jq([
                        "<tr>",
                        " <td class='id'></td>",
                        " <td class='name'></td>",
                        " <td class='action'>",
                        "   <a href='#' class='del'>X</a>",
                        "</td>",
                        "</tr>",
                    ]);
                    officeIdFilter.push(office.id);
                    updateOfficeInputParams();

                    $tr.data("officeId", office.id);
                    $tr.find(".id").text(office.id);
                    $tr.find(".name").text(office.campus + " " + office.name);
                    $tr.find(".del").click(function(e) {
                        util.arrayRemove(officeIdFilter, office.id);
                        updateOfficeInputParams();
                        e.preventDefault();
                        $tr.remove();
                    });
                    $table.append($tr);
                })
            });
        }
        function updateOfficeInputParams() {
            var userId = currentUser ? currentUser.id : null;
            $officeInput.data("params", {
                except: officeIdFilter.concat([userId]),
            });
        }
    }
}

window.addEventListener("load", function() {
    dispatch.setup($("section#dispatch"));
});

