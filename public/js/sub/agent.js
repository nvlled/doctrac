
window.addEventListener("load", function() {
    var $container = $("section#agent");
    var $viewDoc = $container.find("#view-document");
    var $radios = $container.find(".radios input[type=radio]");
    var $mainList = $container.find("div.main.list");

    var currentUser = null;
    var currentDoc = null;

    $("div.list").removeClass("hidden").hide();
    $viewDoc.hide();

    var loaders = {
        all: api.office.allRoutes,
        incoming: api.office.incoming,
        delivering: api.office.delivering,
        processing: api.office.processing,
        final: api.office.final,
    }

    api.user.self().then(setUser);
    setupRadios();

    function setupRadios() {
        $radios.click(function() {
            var $r = $(this);
            $r.change(function() {
                uncheck();
                $r[0].checked = true;
                loadMainList(this.value);
            });
        });
    }
    function uncheck() {
        $radios.each(function() {
            this.checked = false;
        });
    }

    function loadMainList(name) {
        name = name || $radios.filter(function(i, radio) {
            return radio.checked;
        }).val();
        if ( ! name) {
            $(".radios input[value=all]")[0].checked = true;
            name = "all";
        }

        var loader = loaders[name];
        if (!loader) {
            console.warn("loader not found:", name);
            return;
        }
        loadList($mainList, {}, loader);
    }

    function listenEvents() {
        var channel = 'App.User.' + currentUser.id;
        UI.listenEvents(channel, function(notification) {
            console.log("reloading list");
            loadMainList();
        });
    }

    function setUser(user) {
        currentUser = user;
        if (!user) {
            $container.find(".office-id").text("_");
            $container.find(".office-name").text("____");
        }
        listenEvents();
        $container.find(".office-id").text(user.username);
        $container.find(".office-name").text(user.office_name);

        var userId = currentUser ? currentUser.id : null;
        api.user.seenRoutes({userId: userId})
           .then(function(seen) {
               //loadIncoming(seen); TODO
           });

        loadMainList();

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

    function loadList($list, seen, loader) {
        if (!currentUser)
            return;

        var $ul = $list.find("ul");

        $ul.html("");
        $list.find(".none").addClass("hidden");
        $list.find(".loading").removeClass("hidden");
        $list.show();

        Promise.all([
            loader({officeId: currentUser.officeId}),
        ]).then(function(values) {
            $ul.html("");
            $list.find(".loading").addClass("hidden");
            var data = values[0];

            if (!data || data.errors)
                return;
            if (data.length == 0) {
                $list.find(".none").removeClass("hidden");
                return;
            }

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

                //if ((!seen[routeId]
                //    || !util.arrayContains(seen[routeId], info.status))
                //   ) {
                //    $li.addClass("new");
                //}

                $ul.append($li);
            });
        });
    }
});
