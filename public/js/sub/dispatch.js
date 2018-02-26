
var dispatch = {
    setup: function($container) {
        var $message = $container.find(".message");
        var $btnSend = $container.find("button.send");
        var $userName = $container.find(".user-name");
        var $userOffice = $container.find(".user-office");

        var officeSel = null;

        var currentUser = null;
        api.user.change(setCurrentUser);
        api.user.self()
            .then(setCurrentUser);

        setupSendButton();

        function setCurrentUser(user) {
            currentUser = user;
            if (user) {
                $userName.text(user.firstname + " " + user.lastname);
                $userOffice.text(user.office_name);

                officeSel = new UI.OfficeSelection(
                    $container.find("div.office-selection"),
                    {
                        officeId: currentUser.officeId,
                        campusId: currentUser.campus_id,
                        gateway:  currentUser.gateway,
                    }
                );
            } else {
                $userName.text("");
                $userOffice.text("");
            }
        }

        function setupSendButton() {
            $btnSend.click(function() {
                $btnSend.attr("disabled", true);
                $message.text("");
                UI.clearErrors($container);

                var officeIds = officeSel.getSelectedIds();

                var doc = {
                    userId: currentUser ? currentUser.id : null,
                    title: $container.find(".title").val(),
                    details: $container.find(".details").val(),
                    officeIds: officeIds,
                    type: getDispatchType(),
                }
                $btnSend.text("sending...");
                api.doc.send(doc, function(resp) {
                    if (resp.errors) {
                        $btnSend.text("Send");
                        $btnSend.attr("disabled", false);
                        UI.showErrors($container, resp.errors);
                    } else {
                        var trackingId = resp.trackingId;
                        var fileInput = $container.find("input[name=attachment]")[0];
                        var file = fileInput.files[0];
                        uploadFile(trackingId, file).then(function() {
                            $message.text("document sent: " + trackingId);
                            officeSel.clear();
                            $container.find("form")[0].reset();
                            $btnSend.text("Send");
                            $btnSend.attr("disabled", false);

                            util.redirectRoute("view-routes",{
                                trackingId: trackingId,
                            });
                        });
                    }
                });
            });
        }

        function uploadFile(trackingId, file) {
            if (file) {
                $btnSend.text("uploading file...");
                return api.doc.setAttachment({
                    trackingId: trackingId,
                    filename: file.name,
                    filedata: file,
                }).then(function(resp) {
                    if (resp && resp.errors)
                        return UI.showErrors($container, resp.errors);
                });
            } else {
                $btnSend.text("Send");
                $btnSend.attr("disabled", false);
                return Promise.resolve();
            }
        }

        function getDispatchType() {
            return $container.find("input[name=dispatch-type]:checked").val();
        }

    }
}

window.addEventListener("load", function() {
    dispatch.setup($("section#dispatch"));
});

