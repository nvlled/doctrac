
@extends("layout2")

@section("contents")
<section>
<div class='container'>
    <div class='header'>
        <h2 class=''>
        </h2>
    </div>
    <div class='body'>
        body
    </div>
</div>
<style>
.header {
    grid-area: header;
    background-color: #030;
}
.body {
    grid-area: body;
    background-color: #003;
}
.container {
    width: 500px
    height: 500px;
    display: grid;
    grid-template-columns: auto;
    grid-template-rows: 1fr 9fr;
    grid-template-areas:
        "header header . ."
        "body   body   body body";
}
</style>
</section>

<section>
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
