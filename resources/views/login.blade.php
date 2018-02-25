
@extends("layout")

@section("contents")
<section id="session">
<form class="login pure-form pure-form-aligned">
    <fieldset>
        <div class="pure-control-group">
            <label for="name">Username</label>
            <input id="name" name="username" required type="text" placeholder="Username">
            <span class="pure-form-message-inline"></span>
        </div>

        <div class="pure-control-group">
            <label for="password">Password</label>
            <input id="password" name="password" required type="password" placeholder="Password">
            <span class="pure-form-message-inline"></span>
        </div>

        <div class="pure-controls">
            <p class='msg'></p>
            <button type="submit" class="pure-button pure-button-primary">Login</button>
        </div>
    </fieldset>
</form>
<script>
window.addEventListener("load", function() {
    var $loginForm = $("form.login");
    $loginForm.submit(function(e) {
        e.preventDefault();
        var data = util.getFormData($loginForm);
        api.user.login(data).then(function(user) {
            console.log("login response", user);
            if (user) {
                $loginForm
                    .find(".msg")
                    .text("login successful")
                    .show();
                util.redirect("/");
            } else {
                $loginForm.find(".msg")
                    .text("invalid username or password")
                    .show()
                    .delay(1000)
                    .fadeOut();
            }
        });
    });
});
</script>
</section>
@endsection
