
@extends("layout")

@section("contents")
@php
    $data = $data ?? optional();
@endphp

<section id="offices" class="container">
<h2 class="text-center">Account Management</h2>
<div class="row">
    <div class="col-7">
    <h3>New Account</h3>
    <form method="POST">
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
        <div class="form-group row text-danger">
            <div class="offset-3 col">
                {{$error ?? ""}}
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-3 col">
            <button type="submit" class="w-25 create btn btn btn-primary">create</button>
            </div>
        </div>
    </form>
    </div>
</div>
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
            <tr>
                <td>{{$user->username}}</td>
                <td>{{$user->office_name ?? "(none)"}}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</section>
@endsection
