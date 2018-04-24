

@extends("layout")

@section("contents")
<section id="change-pass">

<form name="change-pass" method="POST">
    {{csrf_field()}}
@php
    $errors = $errors ?? [];
    $data = $data ?? [];
@endphp
<fieldset class=''>
    @include("sub.error-list", $errors)
    <div class="">
        <label for="oldpass">Old password</label>
        <input id="oldpass" value="{{@$data['oldpass']}}" name="oldpass" required type="password" placeholder="password">
        <span class=""></span>
    </div>
    <div class="">
        <label for="newpass1">New password</label>
        <input id="newpass1" value="{{@$data['newpass1']}}" name="newpass1" required type="password" placeholder="password">
        <span class=""></span>
    </div>
    <div class="">
        <label for="newpass2">Repeat password</label>
        <input id="newpass2" value="{{@$data['newpass2']}}" name="newpass2" required type="password" placeholder="password">
        <span class=""></span>
    </div>
    <div class="">
        <label class="red" for=""></label>
        <button type="submit" class="change-pass pure-button pure-button-default">
            Submit
        </button>
    </div>
</fieldset>
</form>
<style>
form[name=change-pass] label {
    text-align: right;
    display: inline-block;
    width: 200px;
}
</style>
<script>
var $form = $("form[name=change-pass");
var $btnChangePass = $form.find("button.change-pass");
$btnChangePass.click(function(e) {
    return;
    //e.preventDefault();
    api.user.changePassword({
        oldpass: $("input#oldpass").val(),
        newpass1: $("input#newpass1").val(),
        newpass2: $("input#newpass2").val(),
    }).then(function(resp) {
    });
});
</script>
</section>

@endsection
