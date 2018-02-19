<!DOCTYPE html>
<html lang="en">
<head>
    <title>*</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{asset('css/site.css')}}">
    <link rel="stylesheet" href="{{asset('css/pure-min.css')}}">
    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/events.js')}}"></script>
    <script src="{{asset('js/util.js')}}"></script>
    <script src="{{asset('js/ui.js')}}"></script>
    <script src="{{asset('js/api.js')}}"></script>
</head>
<body>

@yield("scripts")
<script src="{{asset('js/autocomplete.js')}}"></script>
<script src="{{asset('js/localSave.js')}}"></script>
<div id="json hidden">
    <input type='hidden' data-url='/api/user/self'>
</div>
</body>
</html>
