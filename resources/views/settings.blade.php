
@extends("layout")

@section("contents")
<section id="settings">
<form class="settings pure-form pure-form-aligned">
    {{ csrf_field() }}
    <fieldset>
        <div class="pure-control-group">
            <label for="name">Email</label>
            <input id="email" name="email" required type="email" placeholder="email">
            <span class="pure-form-message-inline"></span>
        </div>

        <div class="pure-control-group">
            <label for="mobileno">Mobile #</label>
            <input id="mobileno" name="mobileno" required placeholder="090XXXXXXX">
            <span class="pure-form-message-inline"></span>
        </div>

        <div class="pure-controls">
            <p class='msg'></p>
            <button type="submit" class="logout pure-button pure-button-secondary">
                Logout
            </button>
            <button type="submit" class="save pure-button pure-button-primary">Save Changes</button>
        </div>
    </fieldset>
    <script>
    var $settingsForm = $("form.settings");
    var $btnLogout = $settingsForm.find("button.logout");
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
    </div>
    <script>
    var $changePassForm = $("form.change-pass");
    var $fieldset = $changePassForm.find("fieldset");
    var $btnShow = $changePassForm.find("button.show-fields")

        $btnShow.click(function(e) {
            e.preventDefault();
            $fieldset.removeClass("hidden").show();
            $btnShow.hide();
        });
    </script>
</form>
</section>
@endsection
