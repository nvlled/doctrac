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
    <div class="prefetch hidden">
        @yield("prefetch")
    </div>
    <header class='site'>
        <h1 class='center'>
            <a href="/">Qbphzrag Genpxre</a>
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
                 <li><a class='notifications' href='/notifications'>3 🌎</a></li>
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

        @yield("contents")
    </div>
    @yield("scripts")
    <script src="{{asset('js/combobox.js')}}"></script>
    <script src="{{asset('js/autocomplete.js')}}"></script>
    <script src="{{asset('js/localSave.js')}}"></script>
</body>
</html>
