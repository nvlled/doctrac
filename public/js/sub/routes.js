window.addEventListener("load", function() {
    var $container = $("section#doc-history");
    var $input = $container.find("input.trackingId");
    var $table = $container.find("table");
    var $btnAction = $container.find("button.action");
    var $docTitle = $container.find(".title > .contents");
    var $docType = $container.find(".type");
    var $docDetails = $container.find(".details");
    var $sendData = $container.find(".send-data");
    var $selOffices = $container.find("select.offices");
    var $annots = $container.find(".annots");
    var $origin = $container.find(".origin");
    var currentUser = null;

    var table = UI.createTable($table, {
        cols: ["id", "office_name", "status"],
        colNames: {
            "office_name": "office name",
            "sel": "",
        },
    });

    fetchOffices();
    $sendData.hide();
    $btnAction.hide();

    api.user.self()
       .then(function(user) {
           currentUser = user;
       });

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
        req.then(function() {
            loadDocument();
        });
    });

    $input.change(loadDocument);
    api.user.change(function(user) {
        currentUser = user;
        loadDocument();
    });

    loadDocument();

    var currentDocument;
    function loadDocument() {
        var id = $input.val();

        var params = {trackingId: id};
        api.doc.get(params, function(doc) {
            UI.clearErrors($container);
            table.clearData();
            clearDocInfo(doc);
            $btnAction.hide();
            $sendData.hide();

            if (doc.errors) {
                UI.showErrors($container, doc.errors);
                return;
            }
            if (!doc) {
                return;
            }
            currentDocument = doc;
            updateDocInfo(doc);
            updateAction(doc);
            updateOfficeSelection(doc);

            if (doc.type == "parallel") {
                loadParallelRoutes(id);
            } else {
                loadSerialRoutes(id);
            }
        });
    }

    function loadParallelRoutes(id) {
        var params = {trackingId: id};
        Promise.all([
            api.route.parallel(params),
            api.route.origins(params),
        ]).then(function(values) {
            var data = values[0];
            var origins = values[1];

            if (origins.length > 0) {
                $origin.removeClass("hidden");
                $origin.show();
                $origin.find(".contents").text(
                    origins[0].office_name
                );
            }
            table.loadData(data);
            initRows();
        });
    }

    function loadSerialRoutes(id) {
        $origin.hide();
        api.route.serial({trackingId: id})
           .then(function(data) {
               table.loadData(data);
               initRows();
           });
    }

    function initRows() {
        table.eachRow(function($tr) {
            if (!currentUser)
                return;
            var officeId = currentUser.officeId;
            if (officeId && $tr.data("value").officeId == officeId)
                $tr.addClass("sel");

            addDetails($tr);
        });
    }

    function addDetails($tr) {
        var route = $tr.data("value");
        if (!route)
            return;
        var colspan = table.cols.length;

        var $trDetails = util.jq([
            "<tr class='no-sel'>",
            "<td class='' colspan="+colspan+"'>",
            "<pre class='recv'>",
            route.detailed_info,
            "</pre>",
            "</td>",
            "</tr>",
        ]);

        $trDetails
            .hide()
            .insertAfter($tr);

        $tr.click(function() {
            $trDetails.toggle();
        });
        $trDetails.click(function() {
            $trDetails.hide();
        });
    }

    function forwardDocument() {
        var user = currentUser;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            annotations: $annots.val(),
            trackingId: $input.val(),
        }
        return api.doc.forward(params);
    }

    function receiveDocument() {
        var user = currentUser;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            trackingId: $input.val(),
        }
        return api.doc.receive(params);
    }

    function abortSendDocument() {
        var user = currentUser;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            trackingId: $input.val(),
        }
        return api.doc.abortSend(params);
    }

    function updateOfficeSelection(doc) {
        var param = {
            trackingId: doc.trackingId,
        }
        var p1 = api.doc.currentRoutes(param);
        var p2 = api.doc.nextRoutes(param);
        Promise.all([p1, p2]).then(function(values) {
            var routes = values[0];
            var nextRoutes = values[1];
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

    function fetchOffices() {
        api.office.fetch(function(offices) {
            $selOffices.html("");
            offices.forEach(function(off) {
                var $option = $("<option>");
                $option.val(off.id);
                $option.text(off.name + " " + off.campus);
                $selOffices.append($option);
            });
        });
    }

    function clearDocInfo(doc) {
        updateDocInfo({
            title: "---",
            details: "(no mathching document found)",
            type: "*",
        });
    }
    function updateDocInfo(doc) {
        $docTitle.text(doc.title);
        $docType.text(doc.type);
        $docDetails.text(doc.details);
    }

    function updateAction(doc) {
        if (!currentDocument)
            return;

        if (!currentUser) {
            $btnAction.hide();
            return;
        }
        var params = {
            officeId: currentUser.officeId,
            trackingId: doc.trackingId,
        }
        api.office.actionFor(params, function(resp) {
            $btnAction.show();
            console.log("currentUser", currentUser);
            console.log("doc", doc);
            console.log("action", resp);
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
