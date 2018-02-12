
window.addEventListener("load", function() {
    var $container = $("section#agent");
    var $viewDoc = $container.find("#view-document");
    var $incomingList = $container.find("ul#incoming");
    var $heldList = $container.find("ul#held");
    var $dispatchedList = $container.find("ul#dispatched");
    var $finalList = $container.find("ul#final");

    var currentUser = null;
    var currentDoc = null;

    api.user.self().then(setUser);
    api.user.change(setUser);
    api.doc.change(function() {
        setUser(currentUser);
    });

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
        loadIncoming();
        loadHeld();
        loadDispatched();
        loadFinal();
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
        $viewDoc.find(".annotations").text(info.annotations);

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

    function loadIncoming() { return loadList($incomingList, api.office.incoming) }
    function loadHeld() { return loadList($heldList, api.office.held) }
    function loadDispatched() { return loadList($dispatchedList, api.office.dispatched) }
    function loadFinal() { return loadList($finalList, api.office.final) }

    function loadList($list, loader) {
        if (!currentUser)
            return;
        $list.html("");
        loader({officeId: currentUser.officeId})
           .then(function(data) {
               $list.html("");
               if (!data || data.errors)
                   return;
               if (data.length == 0) {
                   $list.parent().hide();
                   $list.html("<em>(none)</em>");
                   return;
               }
               $list.parent().show();
               data.forEach(function(info) {
                   var $li = $("<li><a href='#view-document'></a></li>");
                   var $a = $li.find("a");
                   var id = info.document_type == "serial"
                        ? info.trackingId
                        : info.trackingId + "-" + info.pathId;
                   var text = util.interpolate(
                       "({trackingId}) {title}",
                       {trackingId: id, title: info.document_title}
                   );
                   $a.text(text);
                   $a.click(function(e) {
                       e.preventDefault();
                       viewDocument(info);
                       $container.find("li.sel").removeClass("sel");
                       $li.addClass("sel");
                   });
                   $list.append($li);
               });
           });
    }
});
