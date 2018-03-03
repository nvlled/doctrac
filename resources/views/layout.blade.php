<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>doctrac</title>
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
                <li><a href='/settings'>☺</a></li>
                <li><a href='/admin'>#</a></li>
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
                        @if ($notifCount)
                        <span class='count'>{{$notifCount}}</span>
                        @endif
                        <img src="{{asset('images/notify.png')}}">
                    </a>
                </li>
                 @endif
            </ul>
        </nav>

        @if (\Flash::has())
        <div class='flash-success'>
            @foreach (\Flash::getAll() as $msg)
                <span class="icon">♫</span>
                <span class="msg">{{$msg}}</span><br>
            @endforeach
        </div>
        @endif

        @if (\Flash::hasError())
        <div class='flash-error'>
            @foreach (\Flash::errorAll() as $msg)
                <span class="icon">✗</span>
                <span class="msg">{{$msg}}</span><br>
            @endforeach
        </div>
        @endif

        <div class='site-contents'>
        @yield("contents")
        </div>
    </div>
    <footer>
        <a href="/about">about</a> |
        copyright © 2018
    </footer>

    @yield("scripts")
    <script src="{{asset('js/combobox.js')}}"></script>
    <script src="{{asset('js/autocomplete.js')}}"></script>
    <script src="{{asset('js/autologout.js')}}"></script>
    <script src="{{asset('js/localSave.js')}}"></script>
</body>
</html>
