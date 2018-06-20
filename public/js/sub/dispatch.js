
var dispatch = {
    setup: function($container) {
        var $message = $container.find(".message");
        var $btnSend = $container.find("button.send");
        var $userName = $container.find(".user-name");
        var $userOffice = $container.find(".user-office");

        var currentUser = null;
        api.user.change(setCurrentUser);
        api.user.self().then(setCurrentUser);

        setupSendButton();
        setupOfficeSel();

        var officeGraph;
        var officeSel;

        function setupOfficeSel() {
            Promise.all([
                OfficeGraph.fetch(),
                api.office.self(),
            ]).then(function(values) {
                var graph = values[0];
                var currentOffice = values[1];
                officeGraph = graph;
                var offices = graph.getOffices();

                console.log("current office", currentOffice);
                officeSel = RouteCreate(graph, {
                    showTable: true,
                    currentOffice:  currentOffice,
                });
                var vm = officeSel.vm;
                vm.mount(document.querySelector("div.dom"));
                UI.hideLoadingMeow();
            });
        }

        function setCurrentUser(user) {
            currentUser = user;
            if (user) {
                $userName.text(user.firstname + " " + user.lastname);
                $userOffice.text(user.office_name);
            } else {
                $userName.text("");
                $userOffice.text("");
            }
        }

        function setupSendButton() {
            $btnSend.click(function() {
                if (!confirm("Proceed sending document?"))
                    return false;

                $btnSend.attr("disabled", true);
                $message.text("");
                UI.clearErrors($container);
                UI.showLoadingMeow();

                var officeIds = officeSel.getRowIds();
                var type = officeSel.getType();

                var doc = {
                    userId: currentUser ? currentUser.id : null,
                    title: $container.find(".title").val(),
                    details: $container.find(".details").val(),
                    classification: $container.find(".classification").val(),
                    annotations: $container.find(".annotations").val(),
                    officeIds: officeIds,
                    type: type,
                }
                api.doc.send(doc, function(resp) {
                    UI.hideLoadingMeow();
                    if (resp.errors) {
                        $btnSend.text("Send");
                        $btnSend.attr("disabled", false);
                        UI.showErrors($container, resp.errors);
                    } else {
                        var trackingId = resp.trackingId;
                        var fileInput = $container.find("input[name=attachment]")[0];
                        var file = fileInput.files[0];
                        UI.uploadFile(trackingId, $btnSend, file, $container).then(function() {
                            $message.text("document sent: " + trackingId);
                            $container.find("form")[0].reset();
                            $btnSend.text("Send");
                            $btnSend.attr("disabled", false);

                            util.redirectRoute("view-routes",{
                                trackingId: trackingId,
                            });
                        }).fail(function(e) {
                            $btnSend.text("redirecting...");
                            $btnSend.attr("disabled", true);
                            setTimeout(function() {
                                api.util.urlFor({
                                    routeName: "view-routes",
                                    trackingId: trackingId,
                                }).then(function(resp) {
                                    window.location = resp.url;
                                });
                            }, 2000);
                        });
                    }
                });
            });
        }

    }
}

window.addEventListener("load", function() {
    dispatch.setup($("section#dispatch"));
});
