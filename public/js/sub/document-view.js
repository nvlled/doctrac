
window.addEventListener("load", function() {
    var $container = $("section#document");
    var $viewDoc = $container.find("#view-document");
    var $sendData = $container.find(".send-data");
    var $btnAction = $container.find("button.action");
    var $annots = $container.find(".annots");
    var $docAttachment = $container.find(".attachment a");

    var currentUser = null;
    var currentDoc = null;
    var officeSel = null;

    $sendData.removeClass("hidden").hide();
    api.user.self().then(setUser);
    api.user.change(setUser);
    setupButtonAction();

    function setUser(user) {
        currentUser = user;
        if (!user) {
            $container.find(".office-id").text("_");
            $container.find(".user-name").text("____");
            $container.find(".office-name").text("____");
        } else {
            officeSel = new UI.OfficeSelection(
                $container.find("div.office-selection"),
                {
                    officeId: currentUser.officeId,
                    campusId: currentUser.campus_id,
                    gateway:  currentUser.gateway,
                    hideTable: true,
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
        $btnAction.hide();

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
            return;
        }
        var params = {
            officeId: parseInt(officeSel.getOfficeId()),
            annotations: $annots.val(),
            routeId: route.id,
        }
        return api.route.forward(params)
                  .then(function() { location.reload(); });
    }

    function receiveDocument() {
        var user = currentUser;
        var trackingId = currentDoc.trackingId;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt(officeSel.getOfficeId()),
            trackingId: currentDoc.trackingId,
        }
        return api.doc.receive(params)
                  .then(function() { location.reload(); });
    }

    function abortSendDocument() {
        var user = currentUser;
        var trackingId = currentDoc.trackingId;
        var route = util.parseJSON($("input#document").val());
        if (!route)
            return Promise.resolve();
        var params = {
            routeId: route.id,
        }
        return api.route.abortSend(params)
                  .then(function() { location.reload(); });
    }

    function setupButtonAction() {
        $btnAction.click(function(e) {
            e.preventDefault();
            var action = $btnAction.data("action");
            var req;
            switch (action) {
                case "send"  : req = forwardDocument(); break;
                case "recv"  : req = receiveDocument(); break;
                case "abort" : req = abortSendDocument(); break;
                default:
                    return;
            }
        });
    }

    function updateButtonAction() {
        $sendData.hide();
        $btnAction.hide();
        if (!currentUser)
            return;
        var route = util.parseJSON($("input#document").val());
        var params = {
            officeId: currentUser.officeId,
            routeId:  route ? route.id : -1,
        }
        api.office.actionForRoute(params, function(resp) {
            console.log("action for", resp);
            $btnAction.show();
            $btnAction.data("action", resp);
            switch(resp) {
                case "send":
                    $btnAction.text("send");
                    $sendData.show();
                    break;
                case "recv": $btnAction.text("receive");break;
                case "abort": $btnAction.text("abort send");break;
                default:
                    $btnAction.hide();
            }
        });
    }
});
