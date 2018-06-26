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

    @php
        $user = optional(Auth::user());
    @endphp
    <body>
        <header id="site" class="">
            <!-- Fixed navbar -->
            <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                <a class="navbar-brand" href="/">
                    <div>
                        <img src="{{asset('images/logo.png')}}" class="logo">
                        <div class="align-top site-desc" class="text-small">DOCUMENT TRACKING SYSTEM</div>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" 
                                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{optional(Auth::user())->username}}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                @if ($user->office)
                                <a class="dropdown-item" href="/">documents</a>
                                @endif
                                @if ($user && optional($user->office)->gateway)
                                    <a class="dropdown-item" href="/dispatch">dispatch</a>
                                @endif
                                @if ($user && $user->isAdmin())
                                <a class="dropdown-item" href="/admin">manage accounts</a>
                                <a class="dropdown-item" href="/admin/offices">manage offices</a>
                                @endif
                                <a class="dropdown-item" href="/settings">settings</a>
                                <a class="dropdown-item" href="/logout">logout</a>
                            </div>
                        </li>
                        @if ($user->office)
                            @php
                                $notifCount = Notif::countUnread();
                                $has = $notifCount > 0 ? "has" : "";
                            @endphp
                            <li class="nav-item">
                                <button href="/notifications" type="button" class="btn btn-{{$notifCount > 0 ? 'primary' : 'outline-info'}}"
                                    onclick="location='/notifications'">
                                    <i class="fas fa-globe"></i> <span class="badge badge-dark">{{$notifCount}}</span>
                                </button>
                            </li>
                        @endif
                    </ul>
                    @if (!request()->is("search"))
                    <form action="/search" method="POST" class="form-inline mt-2 mt-md-0">
                        {{ csrf_field() }}
                        <input name="trackingId" class="form-control form-control-sm mr-sm-2" type="text" placeholder="tracking number" aria-label="Search">
                        <button class="btn btn-outline-info my-2 my-sm-0 btn-sm " type="submit">search</button>
                    </form>
                    @endif
                    <script>
                    </script>
                </div>
            </nav>
        </header>

        <div class="row notifications">
            @include("incl.notify-success", ["msg" => "{name}", "class" => "templ"])
            @foreach (\Flash::getAll() as $msg)
                @include("incl.notify-success", ["msg" => $msg])
            @endforeach

            @include("incl.notify-error", ["msg" => "{name}", "class" => "templ"])
            @foreach (\Flash::errorAll() as $msg)
                @include("incl.notify-error", ["msg" => $msg])
            @endforeach
        </div>

        <main id="contents" role="main" class="container pt-3">
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
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Login</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
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


