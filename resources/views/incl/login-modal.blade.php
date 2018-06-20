

<form class="login modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Login</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
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
            <script src="{{asset('js/sub/login.js')}}"></script>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn btn-primary">submit</button>
        </div>
    </div>
</form>
