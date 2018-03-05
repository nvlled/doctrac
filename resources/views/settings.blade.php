
@extends("layout")

@section("contents")
<section id="settings">
<form class="change-pass pure-form pure-form-aligned">
    {{ csrf_field() }}
    <fieldset class='hidden'>
        <div class="pure-control-group">
            <label for="oldpass">Old password</label>
            <input id="oldpass" name="oldpass" required type="password" placeholder="password">
            <span class="pure-form-message-inline"></span>
        </div>
        <div class="pure-control-group">
            <label for="newpass1">New password</label>
            <input id="newpass1" name="newpass1" required type="password" placeholder="password">
            <span class="pure-form-message-inline"></span>
        </div>
        <div class="pure-control-group">
            <label for="newpass2">Repeat password</label>
            <input id="newpass2" name="newpass2" required type="password" placeholder="password">
            <span class="pure-form-message-inline"></span>
        </div>
        <div class="pure-controls">
            <button type="submit" class="pure-button pure-button-default">
                Submit
            </button>
        </div>
    </fieldset>
    <div class="pure-controls">
        <button type="submit" class="show-fields pure-button pure-button-default">
            Change password
        </button>
        <button type="submit" class="logout pure-button pure-button-secondary">
            Logout
        </button>
    </div>
    <script>
    var $changePassForm = $("form.change-pass");
    var $fieldset = $changePassForm.find("fieldset");
    var $btnLogout = $changePassForm.find("button.logout");
    var $btnShow = $changePassForm.find("button.show-fields")
    $btnShow.click(function(e) {
        return alert("not yet implemented...");
        e.preventDefault();
        $fieldset.removeClass("hidden").show();
        $btnShow.hide();
    });
    $btnLogout.click(function(e) {
        e.preventDefault();
        api.user.logout()
            .then(function(resp) {
                util.redirect("/login");
            });
    });
    </script>
</form>
<hr>
<form class="settings pure-form pure-form-aligned">
    {{ csrf_field() }}
    <fieldset>
        <div class="pure-control-group">
            <label for="name">Email</label>
            <input id="email" name="email" type="email" placeholder="email">
            <span class="pure-form-message-inline"></span>
        </div>

        <div class="pure-control-group">
            <label for="phoneno">Mobile #</label>
            <input id="phoneno" name="phoneno" placeholder="090XXXXXXX">
            <span class="pure-form-message-inline"></span>
        </div>

        <div class="pure-controls">
            <ul class='msgs'></ul>
            <button type="submit" class="save pure-button pure-button-primary">Save Changes</button>
        </div>
    </fieldset>
    <script>
    var $settingsForm = $("form.settings");
    var $btnSave = $settingsForm.find("button.save");
    $btnSave.click(function(e) {
        e.preventDefault();
        var data = {
            email: $("input#email").val(),
            phone_number: $("input#phoneno").val(),
        };
        api.user.update(data).then(function(resp) {
            if (resp && resp.errors)
                UI.showErrors($settingsForm, resp.errors);
            else
                UI.showMessages($settingsForm, ["settings saved"]);
            console.log(resp);
        });
    });
    api.user.self().then(function(user) {
        if (user) {
            $("input#email").val(user.email);
            $("input#phoneno").val(user.phone_number);
        }
    });
    </script>
</form>
</section>
@endsection
