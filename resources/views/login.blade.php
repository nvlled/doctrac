
@extends("layout")

@section("contents")
<section id="session">
    <h3>Login</h3>
    user ID: <input id="session-userid" name="userId"
        size=7
        class="userId autocomplete"
        placeholder="search for user or office name"
        data-format="{lastname}, {firstname} | ({officeId}) {office_name}"
        data-output="#session .user-info"
        data-url="/api/users/search">
    <br>
    <p class="user-info"></p>
    <script>
    $("#session input#session-userid").change(function() {
        api.user.setSelf({userId: this.value});
    });
    api.user.self()
       .then(function(user) {
           $("#session input#session-userid")
               .val(user.id)
               .change();
       });
    </script>
</section>
@endsection
