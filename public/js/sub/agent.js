
window.addEventListener("load", function() {
    var $container = $("section#agent");
    var $viewDoc = $container.find("#view-document");
    var $incomingList = $container.find("ul#incoming");
    var $processingList = $container.find("ul#processing");
    var $deliveringList = $container.find("ul#delivering");
    var $forwardedList = $container.find("ul#forwarded");
    var $finalList = $container.find("ul#final");

    var currentUser = null;
    var currentDoc = null;

    clearList();
    $("div.list").removeClass("hidden").hide();
    $viewDoc.hide();

    api.user.self().then(setUser);
    api.user.change(setUser);
    api.doc.change(function() {
        setTimeout(function() {
            setUser(currentUser);
        });
    });

    function setUser(user) {
        currentUser = user;
        if (!user) {
            $container.find(".office-id").text("_");
            $container.find(".office-name").text("____");
        }
        $container.find(".office-id").text(user.username);
        $container.find(".office-name").text(user.office_name);

        var userId = currentUser ? currentUser.id : null;
        api.user.seenRoutes({userId: userId})
           .then(function(seen) {
               loadIncoming(seen);
               loadProcessing(seen);
               loadDelivering(seen);
               loadForwarded(seen);
               loadFinal(seen);
           });

        viewDocument(null);
    }

    function viewDocument(info) {
        currentDoc = info;

        if (!info) {
            $viewDoc.hide();
            return;
        }
        $viewDoc.show();

        var id = info.document_type == "serial"
            ? info.trackingId
            : info.trackingId + "-" + info.pathId;
        $viewDoc.find(".trackingId").text(id);
        $viewDoc.find(".title").text(info.document_details);
        $viewDoc.find(".status").text(info.status);
        $viewDoc.find(".details").text(info.document_details);

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

    function clearList() {
        $incomingList.html("");
        $processingList.html("");
        $deliveringList.html("");
        $finalList.html("");
    }

    function loadIncoming(seen) {
        return loadList($incomingList, seen, api.office.incoming);
    }
    function loadProcessing(seen) {
        return loadList($processingList, seen, api.office.processing);
    }
    function loadDelivering(seen) {
        return loadList($deliveringList, seen, api.office.delivering);
    }
    function loadForwarded(seen) {
        return loadList($forwardedList, seen, api.office.forwarded);
    }
    function loadFinal(seen) {
        return loadList($finalList, seen, api.office.final);
    }

    function loadList($list, seen, loader) {
        if (!currentUser)
            return;
        $list.html("");

        Promise.all([
            loader({officeId: currentUser.officeId}),
        ]).then(function(values) {
            var data = values[0];

            $list.html("");
            $list.parent().hide();
            if (!data || data.errors)
                return;
            if (data.length == 0) {
                $list.html("<em>(none)</em>");
                return;
            }
            $list.parent().show();
            var url = "/document/{id}";
            data.forEach(function(info) {
                var $li = $("<li><a href='#view-document'></a></li>");
                var $a = $li.find("a");
                var id = info.document_type == "serial"
                    ? info.trackingId
                    : info.trackingId + "[" + info.id + "]";
                var text = util.interpolate(
                    "({trackingId}) {title}",
                    {id: info.id, trackingId: id, title: info.document_title}
                );
                $a.text(text);

                var routeId = info.id;
                $a.attr("href", util.interpolate(url, {id: routeId}));

                if ((!seen[routeId]
                    || !util.arrayContains(seen[routeId], info.status))
                   ) {
                    $li.addClass("new");
                }

                $list.append($li);
            });
           });
    }
});
