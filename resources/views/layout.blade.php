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

    <link rel="stylesheet" href="{{asset('css/pure-min.css')}}">
    <link rel="stylesheet" href="{{asset('css/site.css')}}">

    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/events.js')}}"></script>
    <script src="{{asset('js/util.js')}}"></script>
    <script src="{{asset('js/ui.js')}}"></script>
    <script src="{{asset('js/api.js')}}"></script>
    @yield("styles")
</head>
<body>
    <header class='site'>
        <div class="prefetch hidden">
            @yield("prefetch")
        </div>
        <h1 class='center'>
            <a href="/">document tracker</a>
        </h1>
        <nav class='main left'>
            <ul class='lstype-none'>
                @if (!Auth::user())
                <li><a href='/login'>◐</a></li>
                @elseif (Auth::user()->privilegeId == 0)
                <li><a href='/settings'>☺</a></li>
                <li class="hidden"><a href='/admin'>#</a></li>
                @endif
            </ul>
        </nav>
    </header>
    <div class="site-wrap">
        <nav class='main right'>
            <ul>
                 @php
                 $user = Auth::user();
                 @endphp
                 @if ($user)
                 <li><a href='/'>{{$user->office_name ?? "home"}}</a></li>
                 <li><a href='/search'>search</a></li>
                 @if ($user && optional($user->office)->gateway)
                 <li><a href='/dispatch'>dispatch</a></li>
                 @endif
                 @php
                    $notifCount = Notif::countUnread();
                    $has = $notifCount > 0 ? "has" : "";
                 @endphp
                 <li><a class='notifications {{$has}}' href='/notifications'>
                        <span class='count {{$notifCount ? "" : "hidden"}}'>{{$notifCount}}</span>
                        <img src="{{asset('images/notify.png')}}">
                    </a>
                </li>
                 @endif
            </ul>
        </nav>

        <div class='flash-success {{\Flash::has() ? "" : "hidden"}}'>
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
                    <span class="msg"></span><br>
                </span>
                <span>
                    <a href="#" class="close">[x]</a>
                </span>
            </div>
        </div>

        <div class='flash-error {{\Flash::hasError() ? "" : "hidden"}}'>
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

        <div class='site-contents'>
        @yield("contents")
        </div>
    </div>
    <footer>
        <a class="red" href="https://github.com/nvlled/doctrac">project repository</a> /
        <a class="red" href="https://gitter.im/doctrac/Lobby">developer chat messaging</a> |
        <a href="/about">about</a> |
        copyright © 2018
    </footer>

    @yield("scripts")
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
    <input id="current-user" type="hidden" value="{{optional(Auth::user())->toJson() ?? ''}}">
</body>
</html>
