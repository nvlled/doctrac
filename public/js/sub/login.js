
window.addEventListener("load", function() {
    var $loginForm = $("form.login");
    $loginForm.submit(function(e) {
        e.preventDefault();
        var data = util.getFormData($loginForm);
        api.user.login(data).then(function(resp) {
            UI.clearErrors($loginForm);
            if (resp) {
                if (resp.errors) {
                    UI.showErrors($loginForm, resp.errors);
                    return;
                }

                if (resp.okay) {
                    $loginForm
                        .find(".msg")
                        .text("login successful")
                        .show();
                    util.redirect("/");
                    return;
                }
            }

            var count = "(" + (resp.attempts||0) + ")";
            UI.showErrors($loginForm, ["invalid username or password " + count])
        });
    });
});
