
@extends("layout")

@section("contents")
@php
    $data = $data ?? optional();
@endphp

<section id="accounts" class="container">
<h2 class="text-center">Account Management</h2>
<div class="add-office">
    <table class="table">
        <thead>
        <tr>
            <th>username</th>
            <th>office</th>
            <th>action</th>
        </tr>
        </thead>
        <tbody>
            @foreach (($users ?? []) as $user)
            <tr class="{{textIf($user->isAdmin(), 'text-danger')}}">
                <td></em>{{$user->username}}</td>
                <td>{{$user->office_name ?? "(none)"}}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="row">
    <form id="accounts" method="POST">
        <h3>New Account</h3>
        {{ csrf_field() }}
        <div class="form-group row">
            <label for="name" class="col-3 col-form-label text-right">Username</label>
            <div class="col-7">
                <input id="name" class="form-control" name="username" required type="text" placeholder="username" value="{{$data->username}}">
            </div>
            <span class="pure-form-message-inline"></span>
        </div>
        <div class="form-group row">
            <label for="password" class="col-3 col-form-label text-right">Password</label>
            <div class="col-7">
                <input id="password" class="form-control" name="password" required type="password" placeholder="">
            </div>
        </div>
        <div class="form-group row">
            <label for="password2" class="col-3 col-form-label text-right">Re-enter Password</label>
            <div class="col-7">
                <input id="password2" class="form-control" name="password2" required type="password" placeholder="">
            </div>
        </div>
        <div class="form-group row">
            <label for="firstname" class="col-3 col-form-label text-right">Fullname</label>
            <div class="col-9">
                <input value="{{$data->firstname}}" id="firstname" class="form-control" name="firstname" placeholder="firstname">
                <input value="{{$data->middlename}}" id="middlename" class="form-control" name="middlename" placeholder="middlename">
                <input value="{{$data->lastname}}" id="lastname" class="form-control" name="lastname" placeholder="lastname">
            </div>
            <div class="col-3">
            </div>
            <div class="col-3">
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-3 col">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="admin" id="admin" value="✓">
                    <label class="form-check-label" for="admin">
                        admin?
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="firstname" class="col-3 col-form-label text-right">Office</label>
            <div class="col-9">
                <input type="hidden" name="officeId">
                <input name="office-name">
                <a class="bracket" href="/admin/offices">new office</a>
            </div>
        </div>
        <div class="form-group row text-danger">
            <div class="offset-3 col">
                <ul class="errors">
                </ul>
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-3 col">
            <button type="submit" class="create btn btn btn-primary">create</button>
            </div>
        </div>
    </form>
</div>

<script src="{{asset('js/awesomplete.min.js')}}"></script>
<script>
api.office.fetch(load);
function load(offices) {
    var $form = $("form#accounts");
    var $officeSel = $form.find("select.offices");

    $officeSel.html("");

    var officeNameInput = $("input[name=office-name]")[0];
    var officeIdInput = $("input[name=officeId]")[0];
    new Awesomplete(officeNameInput, {
        filter: function(obj, input) {
            var off = obj.value;
            var name = off.complete_name || "";
            var matches = !! name.match(new RegExp(input, "i"));
            return matches;
        },
        replace: function(obj, input) {
            this.input.value = obj.value.complete_name;
        },
        item: function(obj, input) {
            var off = obj.value;
            console.log(">", off);
            var $li = $("<li>");
            $li.text(off.complete_name);
            return Awesomplete.ITEM(off.complete_name, input.match(/[^,]*$/)[0]);
        },

        list: offices.map(function(off) {
                return off;
            }),
    });
    
    officeNameInput.addEventListener("awesomplete-select", function(data) {
        console.log(data, data.text.value.id, officeIdInput);
        officeIdInput.value = data.text.value.id;
    });

    (offices||[]).forEach(function(off) {
        var $opt = $("<option>");
        $opt.text(off.complete_name);
        $opt.val(off.id);
        $officeSel.append($opt);
    });

    $form.submit(function(e) {
        e.preventDefault();
        UI.clearErrors($form);
        var formData = util.getFormData($form);
        UI.formWait($form);
        api.user.add(formData).then(function(resp) {
            UI.formIdle($form);
            if (resp.errors) {
                UI.showErrors($form, resp.errors);
                return;
            }
            console.log("add office", resp);
            location.reload();
        });
    });
}
</script>
</section>
@endsection

