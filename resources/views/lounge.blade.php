


@extends("layout")

@section("contents")
<section>
    <div class="right">
        <a class="bracket" href="/lounge/archive">archive</a>
    </div>
    <div class="chat-lounge-container">
    </div>
    <style>

    </style>
    <script src='{{asset("js/view/chat-lounge.js")}}'></script>
    <script>
    var chatLounge
    window.addEventListener("load", function() {
        Promise.all([
            api.user.self(),
            api.lounge.messages(),
        ]).then(function(data) {
            var user = data[0];
            var messages = data[1];
            loadChat(user, messages);
        });
        function loadChat(user, messages) {
            chatLounge = new ChatLounge({
                username: (user || {}).username,
                messages: (messages || []).reverse(),
            });
            chatLounge.vm.mount(document.querySelector(".chat-lounge-container"));
        }
    });
    </script>
</section>
@endsection
