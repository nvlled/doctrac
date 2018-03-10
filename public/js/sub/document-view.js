
window.addEventListener("load", function() {
    var $container = $("section#document");
    var $viewDoc = $container.find("#view-document");
    var $annots = $container.find(".annots");
    var $docAttachment = $container.find(".attachment a");

    var $sendData = $container.find(".send-data");
    var $btnRadios = $container.find("div.radios");
    var $btnSend = $container.find("button.send");
    var $btnRecv = $container.find("button.recv");
    var $btnReject = $container.find("button.reject");
    var $btnFinalize = $container.find("button.finalize");
    var $btnActions = $("button.action");

    var currentUser = null;
    var currentDoc = null;
    var officeSel = null;

    $sendData.removeClass("hidden").hide();
    api.user.self().then(setUser);
    api.user.change(setUser);

    setupButtonAction();
    setupRadios();

    function setUser(user) {
        currentUser = user;
        if (!user) {
            $container.find(".office-id").text("_");
            $container.find(".user-name").text("____");
            $container.find(".office-name").text("____");
        } else {
            if (!currentUser.gateway) {
                $btnReject.removeClass("hidden").show();
            } else {
                $btnFinalize.removeClass("hidden").show();
            }
            officeSel = new UI.OfficeSelection(
                $container.find("div.office-selection"),
                {
                    officeId: currentUser.officeId,
                    campusId: currentUser.campus_id,
                    gateway:  currentUser.gateway,
                    hideTable: !currentUser.gateway,
                }
            );

        }
        $container.find(".user-name").text(user.fullname);
        $container.find(".office-id").text(user.officeId);
        $container.find(".office-name").text(user.office_name);
        loadDocument();
    }

    function loadDocument() {
        var trackingId = $("input#trackingId").val();
        var routeId = $("input#routeId").val();

        api.user.seeRoute({
            userId:  currentUser.id, // TODO: should be read from the session
            routeId: routeId,
        });

        var params = {trackingId: trackingId};
        util.loadJson(
            "input#document",
            api.doc.get(params)
        ).then(function(doc) {
            api.route.next({routeId: routeId})
               .then(function(route) {
                   officeSel.setOffice({
                       officeId: route.officeId,
                       campusId: route.campus_id,
                   });
               });

            viewDocument(doc);
            updateButtonAction();
        });
    }

    function viewDocument(info) {
        currentDoc = info;
        //$btnAction.hide();

        if (!info) {
            $viewDoc.hide();
            return;
        }
        $viewDoc.show();

        var id = info.trackingId
        if (info.type == "serial" && info.pathId != null)
            id += "-"+info.pathId;

        $viewDoc.find(".trackingId").text(id);
        $viewDoc.find(".title").text(info.document_title || "");
        $viewDoc.find(".status").text(info.status);
        $viewDoc.find(".classification").text(info.document_class);

        var $details = $viewDoc.find(".details");
        $details.text(info.document_details);
        UI.breakLines($details);

        if (info.nextId)
            $viewDoc.find(".office").text(
                info.office_name + " ~> " +
                info.next_office_name
            );
        else
            $viewDoc.find(".office").text(info.office_name);

        if (info.attachment_filename) {
            $docAttachment.parent().show();
            $docAttachment.text(info.attachment_filename);
            $docAttachment.attr("href", info.attachment_url);
        } else {
            $docAttachment.parent().hide();
        }

        var $annotations = $viewDoc.find(".annotations");

        if (info.annotations) {
            $annotations.parent().show();
            $annotations.text(info.annotations);
            UI.breakLines($annotations);
        } else {
            $annotations.parent().hide();
        }

        var $seenBy = $viewDoc.find(".seen-by");
        var seenBy = info.seen_by || [];
        if (seenBy.length > 0) {
            $seenBy.parent().show();
            $seenBy.text(
                seenBy.map(function(sr) { return sr.full_name; }).join(", ")
            );
        } else {
            $seenBy.parent().hide();
        }

        $ul = $viewDoc.find(".activities");
        $ul.html("");
        var activities = info.activities;
        if (activities.length) {
            activities.forEach(function(act) {
                var $li = $("<li>");
                $li.text(act);
                $ul.append($li);
            });
        }
    }

    function forwardDocument() {
        var user = currentUser;
        var trackingId = currentDoc.trackingId;
        var route = util.parseJSON($("input#document").val());
        if (!route) {
            console.warn("no route found");
            return Promise.resolve();
        }
        var params = {
            officeId: parseInt(officeSel.getOfficeId()),
            annotations: $annots.val(),
            routeId: route.id,
        }
        return api.route.forward(params)
            .then(function (resp) {
                location.reload();
            });
    }

    function receiveDocument() {
        return api.doc.receive(createAPIParams());
    }

    function finalizeDocument() {
        return api.doc.finalize(createAPIParams());
    }

    function rejectDocument() {
        return api.doc.reject(createAPIParams());
    }

    function createAPIParams() {
        var user = currentUser;
        var trackingId = currentDoc.trackingId;
        return {
            userId: user ? user.id : null,
            officeId: parseInt(officeSel.getOfficeId()),
            trackingId: currentDoc.trackingId,
        }
    }

    function setupButtonAction() {
        makeHandler($btnSend, forwardDocument);
        makeHandler($btnRecv, receiveDocument);
        makeHandler($btnFinalize, finalizeDocument);
        makeHandler($btnReject, rejectDocument);

        function makeHandler($btn, onClick) {
            $btn.click(function(e) {
                e.preventDefault();
                UI.disableButton($btn);
                var action = $btn.data("action");
                UI.clearErrors($container);
                var promise = onClick();
                if (promise) {
                    promise.then(function(resp) {
                        if (resp && resp.errors) {
                            UI.showErrors($container, resp.errors);
                        } else {
                            location.reload();
                        }
                        UI.enableButton($btn);
                    });
                } else {
                    UI.enableButton($btn);
                }
            });
        }
    }

    function setupRadios() {
        $.btnRadios.find("input[type=radio]").change(function() {
            switch (this.value) {
                case "forward":
            }
        });
    }

    function updateButtonAction() {
        $sendData.hide();
        $btnActions.hide();
        $btnRadios.hide();
        $sendData.hide();

        if (!currentUser)
            return;

        var route = util.parseJSON($("input#document").val());
        var params = {
            officeId: currentUser.officeId,
            routeId:  route ? route.id : -1,
        }
        api.office.actionForRoute(params, function(resp) {
            switch(resp) {
                case "send":
                    $btnRadios.show();
                    //$btnAction.text("send");
                    $sendData.show();
                    $btnSend.show();
                    if (currentUser.gateway) {
                        $btnFinalize.show();
                    } else {
                        $btnReject.show();
                    }
                    break;
                case "recv":
                    $btnRecv.show();
                default:
            }
        });
    }
});
