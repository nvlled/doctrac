
@extends("layout")

@section("contents")
<section id="session">
<form class="login pure-form pure-form-aligned">
    <fieldset>
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
            <ul class="errors"></ul>
            <p class='error msg'></p>
            <button type="submit" class="pure-button pure-button-primary">Login</button>
        </div>
    </fieldset>
</form>
<script src="{{asset('js/sub/login.js')}}"></script>
</section>
@endsection
