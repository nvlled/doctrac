
@extends("layout")

@section("contents")
<section id="session">
<form class="form-style-1 login pure-form pure-form-aligned">
    <h2 class="center">Login</h2>
    <div class="pure-control-group">
        <label for="name">Username</label>
        <input id="name" name="username" required type="text" placeholder="">
        <span class="pure-form-message-inline"></span>
    </div>

    <div class="pure-control-group">
        <label for="password">Password</label>
        <input id="password" name="password" required type="password" placeholder="">
        <span class="pure-form-message-inline"></span>
    </div>

    <div class="pure-controls">
        <ul class="error errors"></ul>
        <button type="submit" class="pure-button pure-button-primary">submit</button>
    </div>
</form>
<script src="{{asset('js/sub/login.js')}}"></script>
</section>
@endsection
