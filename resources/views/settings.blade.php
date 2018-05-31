
@extends("layout")

@section("contents")
<section id="settings">
<form class="form-style-1 change-pass pure-form pure-form-aligned">
    <h2 class="center">Settings</h2>
    {{ csrf_field() }}
    <script>
    </script>
</form>
<form class="form-style-1 settings pure-form pure-form-aligned">
    {{ csrf_field() }}
    <div class="pure-control-group">
        <label for="name">Primary Email</label>
        <input id="email" name="email" type="email" placeholder="email">
        <span class="pure-form-message-inline"></span>
    </div>

    <div class="pure-control-group hidden">
        <label for="name">Other emails</label>
        <textarea id="emails" name="emails" rows="8"></textarea>
        <span class="pure-form-message-inline"></span>
    </div>

    <div class="pure-control-group hidden">
        <label for="phoneno">Primary Mobile</label>
        <input id="phoneno" name="phoneno" placeholder="090XXXXXXX">
        <span class="pure-form-message-inline"></span>
    </div>
    <div class="pure-control-group">
        <label for="name">Mobile numbers</label>
        <textarea id="phonenumbers" name="other-phoneno" rows="8"></textarea>
        <span class="pure-form-message-inline"></span>
    </div>
    <div class='pure-controls subtext'>
        <s>To register, send INFO to {{env("GLOBE_CODE")}}.
            To unregister, send STOP.</s>
        <br>
        The globe API balance has unfortunately expired, and<br>
        will for the mean time be disabled.<br>

    </div>

    <div class="pure-controls">
        <ul class='msgs'></ul>
        <button type="submit" class="save pure-button pure-button-primary">Save Changes</button>
    </div>
    <div class="pure-controls right">
        <a href="/change-password">change password</a>
        <button type="submit" class="logout pure-button pure-button-secondary">
            Logout
        </button>
    </div>
    <script>
    var $settingsForm = $("form.settings");
    var $btnSave = $settingsForm.find("button.save");
    var currentOffice = null;
    $btnSave.click(function(e) {
        e.preventDefault();
        UI.clearMessages($settingsForm);
        UI.clearErrors($settingsForm);

        if (!currentOffice)
            return;

        var data = {
            officeId: currentOffice.id,
            email: $("input#email").val(),
            emails: util.splitLines($("textarea#emails").val()),
            phoneno: $("input#phoneno").val(),
            phonenumbers: util.splitLines($("textarea#phonenumbers").val()),
        };
        api.office.updateContactInfo(data).then(function(resp) {
            if (resp && resp.errors)
                UI.showErrors($settingsForm, resp.errors);
            UI.showMessages($settingsForm, ["settings saved"]);
            console.log(resp);
        });
    });
    api.office.self().then(function(office) {
        currentOffice = office;
        if (office) {
            var otherEmails  = (office.other_emails || []).join("\n");
            var otherNumbers = (office.other_phone_numbers || []).join("\n");
            $("input#email").val(office.primary_email);
            $("input#phoneno").val(office.primary_phone_number);
            $("textarea#emails").val(otherEmails);
            $("textarea#phonenumbers").val(otherNumbers);
        }
    });
    var $fieldset = $settingsForm.find("fieldset");
    var $btnLogout = $settingsForm.find("button.logout");
    var $btnShow = $settingsForm.find("button.show-fields")
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
<style>
form {
    max-width: 600px !important;
}
</style>
</section>
@endsection
