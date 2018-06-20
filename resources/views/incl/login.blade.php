

<form action="/login" class="form-style-1 login pure-form pure-form-aligned">
    <div class="form-group row">
        <label for="name" class="col-3 col-form-label text-right">Username</label>
        <div class="col-7">
            <input id="name" class="form-control" name="username" required type="text" placeholder="username">
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
        <div class="offset-3 col-7 text-danger">
            <ul class="error errors"></ul>
        </div>
    </div>

    <div class="form-group row">
        <div class="offset-3 col-3">
            <button type="submit" class="btn btn btn-primary">submit</button>
        </div>
    </div>
</form>
<script src="{{asset('js/sub/login.js')}}"></script>
