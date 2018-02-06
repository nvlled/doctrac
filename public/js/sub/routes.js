window.addEventListener("load", function() {
    var $container = $("section#doc-history");
    var $input = $container.find("input.trackingId");
    var $table = $container.find("table");
    var $btnAction = $container.find("button.action");
    var $docTitle = $container.find(".title > .contents");
    var $docType = $container.find(".type");
    var $docDetails = $container.find(".details");
    var $notes = $container.find(".notes");

    var table = UI.createTable($table, {
        cols: ["id", "office_name", "status"],
        colNames: {
            "office_name": "office name",
        },
        actions: {
            "x": function (index, data, $tr) {
            },
        }
    });

    $notes.hide();
    $btnAction.hide();
    $btnAction.click(function(e) {
        e.preventDefault();
    });

    var url = "/api/routes/list/{trackingId}";
    $input.change(loadDocument);
    $input.keypress(function(e) {
        if (e.key == "Enter" || e.keyCode == 13) {
            loadDocument();
        }
    });
    $("#session input.userId").change(function() {
        setTimeout(loadDocument, 300);
    });//!!!!!!!!!!!!

    var currentDocument;
    function loadDocument() {
        var id = $input.val();
        var params = {trackingId: id};
        api.doc.get(params, function(doc) {
            UI.clearErrors($container);
            table.clearData();
            clearDocInfo(doc);
            $btnAction.hide();
            $notes.hide();

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
            table.url = util.interpolate(url, params);
            table.fetchData();
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
                    $notes.show();
                    break;
                case "recv": $btnAction.text("receive");break;
                case "abort": $btnAction.text("abort send");break;
                default:
                    $btnAction.hide();
            }
        });
    }
});

