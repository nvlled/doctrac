window.addEventListener("load", function() {
    var $container = $("section#doc-history");
    var $input = $container.find("input.trackingId");
    var $table = $container.find("table");
    var $docTitle = $container.find(".title > .contents");
    var $docType = $container.find(".type");
    var $docDetails = $container.find(".details");
    var $selOffices = $container.find("select.offices");
    var $annots = $container.find(".annots");
    var $origin = $container.find(".origin");
    var currentUser = null;

    var table = UI.createTable($table, {
        cols: ["office_name", "status"],
        colNames: {
            "office_name": "office name",
        },
        colMap: {
            "details": function(data, $td) {
                var time = data.arrivalTime || "";
                var sender = data.sender_name || "";
                var recvr = data.receiver_name || "";
                if (!time)
                    return;
                var contents =  util.jq([
                    "<pre>",
                    "time: <span>"+time+"</span>",
                    "sender: <span>"+sender+"</span>",
                    "receiver: <span>"+recvr+"</span>",
                    "</pre>",
                ]);
                $td.append(contents);
                $td.hide();
            },
        },
    });


    api.user.self()
       .then(function(user) {
           currentUser = user;
           loadDocument();
       });
    $input.on("complete", loadDocument);
    api.user.change(function(user) {
        currentUser = user;
        loadDocument();
    });

    loadDocument();

    var currentDocument;
    function loadDocument() {
        var id = $input.data("value");

        var params = {trackingId: id};
        api.doc.get(params, function(doc) {
            UI.clearErrors($container);
            table.clearData();

            if (doc.errors) {
                UI.showErrors($container, doc.errors);
                return;
            }
            clearDocInfo(doc);
            if (!doc) {
                return;
            }
            currentDocument = doc;
            updateDocInfo(doc);

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
        var colspan = table.cols.length-1;

        var $trDetails = util.jq([
            "<tr class='no-sel'>",
            "<td></td>",
            "<td class='details half' colspan="+colspan+"'>",
            "<ul class='recv'>",
            route.activities.map(function(act) {
                return util.interpolate(
                    "<li>{text}</li>",
                    {text: act}
                );
            }).join(""),
            "</ul>",
            "<a href='"+route.link+"'>more info</a>",
            "</td>",
            "</tr>",
        ]);

        $trDetails
            .hide()
            .addClass("no-sel")
            .insertAfter($tr);

        $tr.click(function() {
            $trDetails.toggle();
        });
    }

    function forwardDocument() {
        var user = currentUser;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            annotations: $annots.val(),
            trackingId: $input.data("value"),
        }
        return api.doc.forward(params);
    }

    function receiveDocument() {
        var user = currentUser;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            trackingId: $input.data("value"),
        }
        return api.doc.receive(params);
    }

    function abortSendDocument() {
        var user = currentUser;
        var params = {
            userId: user ? user.id : null,
            officeId: parseInt($selOffices.val()),
            trackingId: $input.data("value"),
        }
        return api.doc.abortSend(params);
    }

    function clearDocInfo(doc) {
        var details = $input.data("value") ? "(no matching document found)" : "";

        updateDocInfo({
            title: "---",
            details: details,
            type: "*",
        });
    }
    function updateDocInfo(doc) {
        $docTitle.text(doc.title);
        $docType.text(doc.type);
        $docDetails.text(doc.details);
    }

});
