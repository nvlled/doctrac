
window.addEventListener("load", function() {
    var $container = $("section#document");
    var $viewDoc = $container.find("#view-document");
    var $sendData = $container.find(".send-data");
    var $btnAction = $container.find("button.action");
    var $annots = $container.find(".annots");
    var $selOffices = $container.find("select.offices");
    var $docAttachment = $container.find(".attachment a");

    var currentUser = null;
    var currentDoc = null;

    api.user.self().then(setUser);
    api.user.change(setUser);
    setupButtonAction();

    function setUser(user) {
        currentUser = user;
        if (!user) {
            $container.find(".office-id").text("_");
            $container.find(".user-name").text("____");
            $container.find(".office-name").text("____");
        }
        $container.find(".user-name").text(user.fullname);
        $container.find(".office-id").text(user.officeId);
        $container.find(".office-name").text(user.office_name);
        loadDocument();
    }

    function loadDocument() {
        var trackingId = $("input#trackingId").val();

        util.loadJson(
            "input#document", 
            api.doc.get({trackingId: trackingId})
        ).then(function(doc) {
            setupOfficeSelection(doc);
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
        $viewDoc.find(".details").text(info.document_details);
        $viewDoc.find(".status").text(info.status);
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
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            annotations: $annots.val(),
            trackingId: currentDoc.trackingId,
        }
        return api.doc.forward(params)
                  .then(function() { util.redirect("/search/"+trackingId)});
    }

    function receiveDocument() {
        var user = currentUser;
        var trackingId = currentDoc.trackingId;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            trackingId: currentDoc.trackingId,
        }
        return api.doc.receive(params)
                  .then(function() { util.redirect("/search/"+trackingId)});
    }

    function abortSendDocument() {
        var user = currentUser;
        var trackingId = currentDoc.trackingId;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            trackingId: currentDoc.trackingId,
        }
        return api.doc.abortSend(params)
                  .then(function() { util.redirect("/search/"+trackingId)});
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
        var params = {
            officeId: currentUser.officeId,
            trackingId: currentDoc.trackingId,
        }
        api.office.actionFor(params, function(resp) {
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

    function setupOfficeSelection(doc) {
        var param = {
            trackingId: doc.trackingId,
        }
        var p1 = api.doc.currentRoutes(param);
        var p2 = api.doc.nextRoutes(param);
        var p3 = api.office.nextOffices({
            officeId: currentUser.officeId
        });
        Promise.all([p1, p2, p3]).then(function(values) {
            var routes = values[0];
            var nextRoutes = values[1];
            var offices = values[2];

            $selOffices.html("");
            offices.forEach(function(off) {
                var $option = $("<option>");
                $option.val(off.id);
                $option.text(off.name + " " + off.campus_name);
                $selOffices.append($option);
            });

            if (routes && routes.map) {
                var officeIds = routes.map(function(r) {
                    return r.officeId;
                });
                disableOffices(officeIds);
            }
            if (nextRoutes && nextRoutes.map) {
                var officeIds = nextRoutes.map(function(r) {
                    return r.officeId;
                });
                selectOffices(officeIds);
            }
        });
    }

    function selectOffices(officeIds) {
        var index = -1;
        var value = -1;
        $selOffices.find("option").each(function(i, opt) {
            var offId = parseInt(opt.value);
            if (officeIds.indexOf(offId) >= 0) {
                opt.selected = true;
                index = i;
                value = offId;
            }
        });
        if (value >= 0)
            $selOffices.val(value);
    }
    function disableOffices(officeIds) {
        $selOffices.find("option").each(function(_, opt) {
            opt.disabled = false;
            var offId = parseInt(opt.value);
            if (officeIds.indexOf(offId) >= 0)
                opt.disabled = true;
        });
    }
});
