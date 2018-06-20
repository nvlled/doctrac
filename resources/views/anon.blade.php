<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="{{asset('css/fontawesome-all.min.css')}}">
        <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/doctrac.css')}}">

        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/popper.min.js')}}"></script>
        <script src="{{asset('js/tooltip.min.js')}}"></script>
        <script src="{{asset('js/lib/domvm.dev.js')}}"></script>
        <script src="{{asset('js/events.js')}}"></script>
        <script src="{{asset('js/util.js')}}"></script>
        <script src="{{asset('js/ui.js')}}"></script>
        <script src="{{asset('js/api.js')}}"></script>
        @yield("styles")

        <title>doctrac</title>
    </head>

    <body>
        <div class="main jumbotron row">
            <div class="col-6">
                <div class="clearfix">
                    <a href="/"><img src="{{asset('images/logo.png')}}" class="float-left logo"></a>
                    <div class="align-top desc" class="float-left">DOCUMENT TRACKING SYSTEM</div>
                </div>
            </div>
            <div class="offset-4 col-2 text-right">
                <button id="popup-login" data-toggle="modal" data-target="#login" 
                    class="btn btn-outline-light my-2 my-sm-0" type="submit">
                    <i class="fas fa-sign-in-alt"></i> login
                </button>
            </div>
        </div>

        <main role="main" class="container">
            @yield("contents")
        </main>

        <footer class="footer fixed-bottom">
            <div class="container">
                <div id="copyright">
                    Copyright 2018 - PSU Document Tracking System
                </div>
            </div>
        </footer>

        <div id="login" class="modal" tabindex="-1" role="dialog">
            @include("incl.login-modal")
        </div>
    </body>

    @yield("scripts")
    <script src="{{asset('bootstrap/js/bootstrap.min.js')}}"></script>

    <script src="{{asset('js/lib/j2c.js')}}"></script>
    <!--<script src="{{asset('material/js/materialize.min.js')}}"></script>-->
    <script src="{{asset('js/combobox.js')}}"></script>
    <!--<script src="{{asset('js/autocomplete.js')}}"></script>-->
    <script src="{{asset('js/autologout.js')}}"></script>
    <!--<script src="{{asset('js/filled.js')}}"></script>-->
    <script src="{{asset('js/localSave.js')}}"></script>
    <script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>
    <script src="{{asset('js/echo.js')}}"></script>
    <script>
    $(function() {
        var channel = 'App.User.' + '{{Auth::id()}}';
        UI.listenEvents(channel, function(notification) {
            console.log("**notification", notification);
            UI.addNotification(notification);
        });
    });
    </script>
    @php
        $currentUser = optional(Auth::user());
        $currentOffice = optional($currentUser->office);
    @endphp
    <input id="current-user" type="hidden"
           value="{{$currentUser->toJson() ?? ''}}">
    <input id="current-office" type="hidden"
           value="{{$currentOffice->toJson() ?? ''}}">

    <script>
    $(function () {
        $('[data-toggle="popover"]').popover()
    });
    </script>

</html>



