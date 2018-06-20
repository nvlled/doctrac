
@extends("layout")

@section("contents")
<section id="settings" class="container">
<form class="form-style-1 settings pure-form pure-form-aligned">
    {{ csrf_field() }}
    <div class="row">
        <label for="name" class="col-form-label col-3 text-right">Primary Email</label>
        <div class="col-8">
        <input id="email" name="email" type="email" placeholder="email" class="form-control">
        </div>
        <span class="pure-form-message-inline"></span>
    </div>

    <div class="row">
        <label for="emails" class="col-form-label col-3 text-right">Other Emails</label>
        <div class="col-8">
        <textarea id="emails" name="emails" class="form-control" rows="6"></textarea>
        </div>
        <span class="pure-form-message-inline"></span>
    </div>

    <div class="row">
        <label for="phoneno" class="col-form-label col-3 text-right">Primary Mobile</label>
        <div class="col-8">
        <input id="phoneno" name="phoneno" type="phoneno" placeholder="090XXXXXX" class="form-control" >
        </div>
        <span class="pure-form-message-inline"></span>
    </div>

    <div class="row">
        <label for="emails" class="col-form-label col-3 text-right">Mobile Numbers</label>
        <div class="col-8">
            <textarea id="phonenumbers" class="form-control" name="other-phoneno" rows="8"></textarea>
        </div>
        <span class="pure-form-message-inline"></span>
    </div>

    <div class='row'>
        <div class="offset-3 col-6">
        <s>To register, send INFO to {{env("GLOBE_CODE")}}.
            To unregister, send STOP.</s>
        The globe API balance has unfortunately expired, and<br>
        will for the mean time be disabled.<br>
        </div>

    </div>

    <div class="row">
        <div class="offset-3 col-6">
        <ul class='msgs'></ul>
        <button type="submit" class="save btn btn-primary">Save Changes</button>
        </div>
    </div>
    <div class="row">
        <div class="offset-3 col-8 text-right">
        <a href="/change-password">change password</a>
        <button type="submit" class="logout btn btn-secondary">
            Logout
        </button>
        </div>
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
                util.redirect("/");
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
