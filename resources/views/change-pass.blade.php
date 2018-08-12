

@extends("layout")

@section("contents")
<section id="change-pass">

<form name="change-pass" method="POST">
    {{csrf_field()}}
@php
    $errors = $errors ?? [];
    $data = $data ?? [];
@endphp
    <div class="row">
        <label for="oldpass" class="col-form-label col-2 text-right">Old password</label>
        <div class="col-5">
            <input id="oldpass" value="{{@$data['oldpass']}}" name="oldpass" required type="password" placeholder="password" class="form-control">
        </div>
        <span class=""></span>
    </div>
    <div class="row">
        <label for="newpass1" class="col-form-label col-2 text-right">New password</label>
        <div class="col-5">
            <input id="newpass1" value="{{@$data['newpass1']}}" name="newpass1" required type="password" placeholder="password" class="form-control">
        </div>
        <span class=""></span>
    </div>
    <div class="row">
        <label for="newpass2" class="col-form-label col-2 text-right">Repeat password</label>
        <div class="col-5">
        <input id="newpass2" value="{{@$data['newpass2']}}" name="newpass2" required type="password" placeholder="password" class="form-control">
        </div>
        <span class=""></span>
    </div>
    <div class="row pl-3">
        <div class="offset-2">
        @include("sub.error-list", $errors)
        </div>
    </div>
    <div class="row">
        <label class="red" for=""></label>

        <div class="offset-2 col-5">
        <button type="submit" class="btn btn-primary change-pass pure-button pure-button-default"> Submit </button>
        </div>
    </div>
</form>
<style>
form[name=change-pass] label {
}
</style>
</section>

@endsection
