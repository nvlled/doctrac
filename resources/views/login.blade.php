
@extends("layout")

@section("contents")
<section id="session">
    <h3>Login</h3>
    user  <input id="session-userid" name="userId"
        size=30
        class="userId autocomplete"
        placeholder="search for user or office name"
        data-format="{lastname}, {firstname} @ {office_name}"
        data-output="#session .user-info"
        data-url="/api/users/search">
    <span class="msg"></span>
    <br>
    <script>
    var $input = $("#session input#session-userid");
    var $msg = $("#session .msg");
    $input.on("complete", function(_, userId) {
        //var userId = ($(this).data("value") || "").trim();
        if (userId) {
            api.user.setSelf({userId: userId});
            $msg.show()
                .text("current user changed")
                .delay(1500)
                .fadeOut(1000);
        }
    });
    api.user.self().then(function(user) {
        if (user) {
            $input.trigger("set-value", user);
        }
    });
    </script>
</section>
@endsection
