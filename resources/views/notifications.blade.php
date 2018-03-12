
@extends("layout")

@section("contents")
<section id="notifications">
<div class="notifs">
    <div class="notif-msg">
    <strong class='num'></strong>
    <span class='contents'></span>
    (<small class='diff'></small>)
    </div>
</div>
<div class="loading-msg">loading...</div>
<div class='center'>
    <button class='hidden'>clear notifications</button>
</div>
</section>
<style>
.notif-msg {
    padding: 10px;
    border-bottom: 1px solid gray;
}
.num {
    padding-right: 10px;
}
</style>
<script>

api.user.notifications().then(function(notifs) {
    $(".loading-msg").remove();
    var $container = $("div.notifs");
    var $template = $("div.notif-msg").detach();
    if (!notifs || notifs.length == 0) {
        $container.append("<h3 class='center'>no notifications</h3>");
        return;
    }
    notifs.forEach(function(notif, i) {
        // 2018, I still make this mistake......
        //for (var i = 0; i < notifs.length; i++) {
        //    var notif = notifs[i];

        var $div = $template.clone();
        if (notif.unread)
            $div.addClass("unread");
        $div.find(".num").text(i+1);
        $div.find(".contents").text(notif.message);
        $div.find(".diff").text(notif.diff);
        $div.click(function() {
            api.user.readNotification({
            id: notif.id,
            }).then(function() {
                util.redirect(notif.url);
            });
        });
        $container.append($div);
    });
});

</script>
@endsection
