<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="{{asset('css/site.css')}}">
    @yield("styles")
</head>
<body>
    <h1>document tracking sisteme</h1>
    <hr>
    <div class="site-wrap">
        @yield("contents")
    </div>
    @yield("scripts")
</body>
</html>
