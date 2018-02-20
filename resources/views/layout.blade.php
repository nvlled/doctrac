<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>doctrac</title>
    <link rel="stylesheet" href="{{asset('css/site.css')}}">
    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/events.js')}}"></script>
    <script src="{{asset('js/util.js')}}"></script>
    <script src="{{asset('js/ui.js')}}"></script>
    <script src="{{asset('js/api.js')}}"></script>
    @yield("styles")
</head>
<body>
    <h1 class='center'>
        <a href="/">doctrac</a>
    </h1>
    <div class="site-wrap">
        <nav class='main right'>
            <ul>
                <li><a href='/'>{{Auth::user()->full_name ?? "home"}}</a></li>
                <li><a href='/login'>login</a></li>
                <li><a href='/search'>search</a></li>
                @if (Auth::user() && optional(Auth::user()->office)->gateway)
                <li><a href='/dispatch'>dispatch</a></li>
                @endif
                <li><a href='/admin'>admin</a></li>
            </ul>
        </nav>
        @yield("contents")
    </div>
    @yield("scripts")
    <script src="{{asset('js/autocomplete.js')}}"></script>
    <script src="{{asset('js/localSave.js')}}"></script>
</body>
</html>
