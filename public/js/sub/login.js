
window.addEventListener("load", function() {
    var $loginForm = $("form.login");
    $loginForm.submit(function(e) {
        e.preventDefault();
        var data = util.getFormData($loginForm);
        api.user.login(data).then(function(user) {
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
