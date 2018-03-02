window.addEventListener("load", function() {
    var $container = $("section#doc-history");
    var $input = $container.find("input.trackingId");
    var $table = $container.find("table");
    var $docTitle = $container.find(".title > .contents");
    var $docType = $container.find(".type");
    var $docDetails = $container.find(".details");
    var $docAttachment = $container.find(".attachment a");
    var $selOffices = $container.find("select.offices");
    var $annots = $container.find(".annots");
    var currentUser = null;

    var table = UI.createTable($table, {
        cols: ["office_name", "status", "time_elapsed"],
        colNames: {
            "office_name": "office name",
            "time_elapsed": "elapsed",
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


    $docTitle.parent().hide()
    $docAttachment.parent().hide()
    api.user.self()
       .then(function(user) {
           currentUser = user;
           var json = $container.find("#document").val();
           var doc = JSON.parse(json);
           if (doc)
               showDocument(doc);
       });

    $input.on("complete", loadDocument);
    api.user.change(function(user) {
        currentUser = user;
        loadDocument();
    });

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
            showDocument(doc);
        });
    }

    function showDocument(doc) {
        clearDocInfo(doc);
        if (!doc) {
            return;
        }
        updateDocInfo(doc);
        $docTitle.parent().show()

        if (doc.type == "parallel") {
            loadParallelRoutes(doc);
        } else {
            loadSerialRoutes(doc);
        }
    }

    function loadParallelRoutes(doc) {
        var id = doc.trackingId;
        var params = {trackingId: id};
        Promise.all([
            api.route.parallel(params),
            api.route.origins(params),
            api.util.urlFor({
                routeName: "view-subroutes",
                trackingId: id,
            })
        ]).then(function(values) {
            var data = values[0];
            var origins = values[1];
            var url = values[2].url;

            var status = getOriginStatus(origins);
            var origin = origins[0];
            if (origin) {
                origin.status = status;
                origin.link = url;
                data.unshift(origin);
            }

            table.loadData(data);
            initRows(doc);
        });
    }

    function getOriginStatus(origins) {
        var delivering = 0;
        var processing = 0;
        var done = 0;
        origins.forEach(function(route) {
            if (route.status == "delivering")
                delivering++;
            else if (route.status == "processing")
                processing++;
            else if (route.status == "done")
                done++;
        });
        if (done == origins.length)
            return "done";
        if (delivering > 0 && processing > 0)
            return "delivering/processing"
        if (delivering > 0)
            return "delivering"
        if (processing > 0)
            return "processing"
        return "*";
    }

    function loadSerialRoutes(doc) {
        var id = doc.trackingId;
        api.route.serial({trackingId: id})
           .then(function(data) {
               table.loadData(data);
               initRows(doc);
           });
    }

    function initRows(doc) {
        table.eachRow(function($tr, i) {
            if (!currentUser)
                return;
            if (i > 0 && doc.type == "parallel") {
                indentRow($tr);
            }
            var officeId = currentUser.officeId;
            if (officeId && $tr.data("value").officeId == officeId)
                $tr.addClass("sel");

            addDetails($tr);
        });
    }

    function indentRow($tr) {
        $tr.addClass("indented");
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
            util.redirect(route.link);
            //$trDetails.toggle();
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

    function clearDocInfo(doc) {
        var details = $input.data("value") ? "(no matching document found)" : "";

        $docTitle.parent().hide()
        $docAttachment.parent().hide()
        updateDocInfo({
            title: "",
            details: details,
            type: "",
        });
    }

    function updateDocInfo(doc) {
        $docTitle.text(doc.title);
        $docType.text(doc.type);

        UI.setText($docDetails, doc.details);
        UI.breakLines($docDetails);

        if (doc.attachment_filename) {
            $docAttachment.parent().show();
            $docAttachment.text(doc.attachment_filename);
            $docAttachment.attr("href", doc.attachment_url);
        } else {
            $docAttachment.parent().hide();
        }
    }
});
