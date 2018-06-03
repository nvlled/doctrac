
window.addEventListener("load", function() {
    var $container = $("section#document");
    var $viewDoc = $container.find("#view-document");
    var $annots = $container.find(".annots");
    var $docAttachment = $container.find(".attachment a");

    var $sendData = $container.find(".send-data");
    var $btnSend = $container.find("button.send");
    var $btnRecv = $container.find("button.recv");
    var $btnReject = $container.find("button.reject");
    var $btnReturn = $container.find("button.return");
    var $btnFinalize = $container.find("button.finalize");
    var $btnActions = $("button.action");

    var currentUser = null;
    var currentDoc = null;
    var officeGraph;
    var officeSel;

    $btnActions.removeClass("hidden").hide();
    $sendData.removeClass("hidden").hide();
    api.user.self().then(setUser);
    api.user.change(setUser);

    setupButtonAction();
    UI.hideLoadingMeow();

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

    function setupOfficeSel(doc) {
        Promise.all([
            OfficeGraph.fetch(),
            api.office.self(),
        ]).then(function(values) {
            var graph = values[0];
            var currentOffice = values[1];
            officeGraph = graph;
            var offices = graph.getOffices();

            // TODO: hide if parallel and current office is not records or main
            //       or better yet, actionFor should be "" when conditions above holds
            var docType = doc.document_type;
            var canParallelSend = docType == "parallel" &&
                !!currentOffice.gateway;

            var nextOffice = graph.getOffice(doc.next_office_id);
            officeSel = RouteCreate(graph, {
                showTable: canParallelSend,
                showType: false,
                showAddButton: canParallelSend,
                currentOffice:  currentOffice,
                type: docType,
                noSelect: doc.document_state == "disapproved",
                selectedOffice: (docType == "serial") && nextOffice ? nextOffice : null,
            });
            var vm = officeSel.vm;
            vm.mount(document.querySelector("div.dom"));
        });
    }

    function loadDocument() {
        var trackingId = $("input#trackingId").val();
        var routeId = $("input#routeId").val();

        api.user.seeRoute({
            userId:  currentUser.id, // TODO: should be read from the session
            routeId: routeId,
        });

        if (currentUser.gateway) {
            var params = {trackingId: trackingId};
            util.loadJson(
                "input#document",
                api.doc.get(params)
            ).then(function(doc) {
                currentDoc = doc;
                api.doc.unfinishedRoutes({trackingId: trackingId})
                    .then(function(routes) {
                        if (!routes) {
                            return;
                        }
                        routes = routes.slice(1);
                        var offices = routes.map(function(route) {
                            var names = route.office_name.split(" ");
                            return {
                                id: route.officeId,
                                name: names[0],
                                campus_name: names[1],
                                campus_id: route.campus_id,
                            }
                        });
                        setupOfficeSel(doc);
                        updateButtonAction();
                    });
            });
        } else {
            var params = {trackingId: trackingId};
            util.loadJson(
                "input#document",
                api.doc.get(params)
            ).then(function(doc) {
                currentDoc = doc;
                setupOfficeSel(doc);
                updateButtonAction();
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

        var officeId = officeSel.getSelectedOfficeId();
        var officeIds = officeSel.getRowIds();
        if (currentDoc.document_type == "serial")
            var officeIds = [officeId].concat(officeIds);
        var params = {
            officeIds: officeIds,
            annotations: $annots.val(),
            routeId: route.id,
        }
        return api.route.forward(params);
    }

    function receiveDocument() {
        return api.doc.receive(createAPIParams());
    }

    function finalizeDocument() {
        return api.route.finalize(createAPIParams());
    }

    function rejectDocument() {
        return api.doc.reject(createAPIParams());
    }

    function createAPIParams() {
        var user = currentUser;
        var trackingId = currentDoc.trackingId;
        var route = util.parseJSON($("input#document").val()) || {};
        return {
            userId: user ? user.id : null,
            officeId: parseInt(officeSel.getSelectedOfficeId()),
            trackingId: currentDoc.trackingId,
            routeId: route.id,
            annotations: $annots.val(),
        }
    }

    function setupButtonAction() {
        makeHandler($btnSend, forwardDocument);
        makeHandler($btnRecv, receiveDocument);
        makeHandler($btnFinalize, finalizeDocument);
        makeHandler($btnReject, rejectDocument);
        makeHandler($btnReturn, forwardDocument);

        function makeHandler($btn, onClick) {
            $btn.click(function(e) {
                if (!confirm("Proceed action?"))
                    return false;

                e.preventDefault();
                UI.disableButton($btn);
                UI.showLoadingMeow();
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
                        UI.hideLoadingMeow();
                    });
                } else {
                    UI.enableButton($btn);
                    UI.hideLoadingMeow();
                }
            });
        }
    }

    function updateButtonAction() {
        $sendData.hide();
        $btnActions.hide();
        $sendData.hide();

        if (!currentUser)
            return;

        var route = util.parseJSON($("input#document").val());
        var params = {
            officeId: currentUser.officeId,
            routeId:  route ? route.id : -1,
        }
        api.office.actionResponseForRoute(params, function(resp) {
            console.log("action for", resp);
            switch(resp) {
                case "send":
                    $sendData.show();
                    $btnSend.show();
                    if (currentDoc.document_type == "serial" &&
                        currentDoc.document_state != "disapproved") {
                        if (currentUser.gateway) {
                            $btnFinalize.show();
                        } else {
                            $btnReject.show();
                        }
                    }
                    break;

                case "recv":
                    $btnRecv.show();
                    break;
            }
        });
    }
});
