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
    var $inputUserId = $("#session input.userId");

    var table = UI.createTable($table, {
        cols: ["id", "office_name", "status"],
        colNames: {
            "office_name": "office name",
        },
    });

    fetchOffices();
    $sendData.hide();
    $btnAction.hide();

    loadSavedInput();

    $btnAction.click(function(e) {
        e.preventDefault();
        forwardDocument();
    });

    var url = "/api/routes/list/{trackingId}";
    $input.change(loadDocument);
    $input.keypress(function(e) {
        if (e.key == "Enter" || e.keyCode == 13) {
            util.storeSet("trackingId", this.value);
            loadDocument();
        }
    });
    $inputUserId.change(function() {
        // TODO: this is just a workaround
        // on sort-of race condition
        setTimeout(loadDocument, 300);
        util.storeSet("userId", this.value);
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

            table.url = util.interpolate(url, params);
            table.fetchData();
        });
    }

    function forwardDocument() {
        var user = api.user.self();
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            annotations: $annots.val(),
            trackingId: $input.val(),
        }
        api.doc.forward(params);
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
                console.log("disable", officeIds);
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

    function loadSavedInput() {
        var userId = util.storeGet("userId")
        if (userId) {
            $inputUserId.val(userId);
            $inputUserId.change();
        }
        $input.val(util.storeGet("trackingId"));
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
        var user = api.user.self();
        if (!user) {
            $btnAction.hide();
            return;
        }
        var params = {
            officeId: user.officeId,
            trackingId: doc.trackingId,
        }
        api.office.actionFor(params, function(resp) {
            $btnAction.show();
            console.log("user", user);
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

