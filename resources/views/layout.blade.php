<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>doctrac</title>

    <!--Import Google Icon Font-->
    <!--<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">-->
    <!--Import materialize.css-->
    <!--<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">-->

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!--
    <link rel="stylesheet" href="{{asset('css/pure-min.css')}}">
    <link rel="stylesheet" href="{{asset('css/site.css')}}">
    -->
    <link rel="stylesheet" href="{{asset('css/util.css')}}">
    <link rel="stylesheet" href="{{asset('css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('css/site.css')}}">
    <link rel="stylesheet" href="{{asset('css/sanwebe-form1.css')}}">

    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/lib/domvm.dev.js')}}"></script>
    <script src="{{asset('js/events.js')}}"></script>
    <script src="{{asset('js/util.js')}}"></script>
    <script src="{{asset('js/ui.js')}}"></script>
    <script src="{{asset('js/api.js')}}"></script>
    @yield("styles")
</head>
<body>
    <div id="site-wrapper">
        <header class='site'>
            <div class="prefetch hidden">
                @yield("prefetch")
            </div>
            <h1 class='site-name'>
                <a class="name" href="/">dtrac</a>
                <small class="desc">document tracking system</small>
            </h1>
            <nav class='main'>
                <ul class='lstype-none left'>
                    @php $user = Auth::user() @endphp
                    @if (!$user)
                    <li><a href='/login'>login</a></li>
                    @else
                    <li><a href='/settings'>{{$user->office_name ?? "home"}}</a></li>
                    @php
                        $notifCount = Notif::countUnread();
                        $has = $notifCount > 0 ? "has" : "";
                    @endphp
                    <li><a class='notifications {{$has}}' href='/notifications'>
                            <span class='count {{$notifCount ? "" : "hidden"}}'>{{$notifCount}}</span>
                            <img class="icon" src="{{asset('images/notify.png')}}">
                        </a>
                    </li>
                    @endif
                </ul>
                <ul class='lstype-none right'>
                    @if (!request()->is("search"))
                    <li class="search">
                        <form method="POST" action="/search" class="form-style-1 inline">
                            {{ csrf_field() }}
                            <input class="search" name="trackingId" 
                                   type="text" size="15" placeholder="tracking-number"><button class="search">
                                <img class="icon" src="{{asset('images/search.png')}}">
                            </button>
                        </form>
                    </li>
                    @endif
                </ul>
            </nav>
        </header>
        <div class="site-contents">

            <div class='flash-success {{\Flash::has() ? "grid" : "hidden"}}'>
                @foreach (\Flash::getAll() as $msg)
                <div class="flex-lr">
                    <span>
                        <span class="icon">♫</span>
                        <span class="msg">{{$msg}}</span><br>
                    </span>
                    <span>
                        <a href="#" class="close">[x]</a>
                    </span>
                </div>
                @endforeach
                <div class="templ hidden flex-lr">
                    <span>
                        <span class="icon">♫</span>
                        <span class="msg">Message here</span><br>
                    </span>
                    <span>
                        <a href="#" class="close">[x]</a>
                    </span>
                </div>
            </div>

            <div class='flash-error {{\Flash::hasError() ? "grid" : "hidden"}}'>
                @foreach (\Flash::errorAll() as $msg)
                <div class="flex-lr">
                    <span>
                        <span class="icon">✗</span>
                        <span class="msg">{{$msg}}</span><br>
                    </span>
                    <span>
                        <a href="#" class="close">[x]</a>
                    </span>
                </div>
                @endforeach
                <div class="templ hidden flex-lr">
                    <span>
                        <span class="icon">✗</span>
                        <span class="msg"></span><br>
                    </span>
                    <span>
                        <a href="#" class="close">[x]</a>
                    </span>
                </div>
            </div>

            <nav class='sub right'>
                <ul class='lstype-none inline right'>
                    @if ($user)
                        <li><a href="/">browse</a></li>
                        @if ($user && optional($user->office)->gateway)
                        <li><a href='/dispatch'>dispatch</a></li>
                        @endif
                        <li><a href='/lounge'>lounge</a></li>
                    @endif
                    @if (optional($user)->admin)
                    <li><a href='/admin'>#</a></li>
                    @endif
                </ul>
            </nav>
            <div class='site-contents'>
            @yield("contents")
            </div>
        </div>

        <footer>
            <a class="red" href="https://github.com/nvlled/doctrac">project repository</a> /
            <a class="red" href="https://gitter.im/doctrac/Lobby">developer chat messaging</a> |
            <a href="#">about</a> |
            copyright © 2018
        </footer>
    </div>

    @yield("scripts")
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
</body>
</html>
