


@php
$errors = $errors ?? [];
@endphp
<ul class="errors">
@foreach($errors as $k => $v)
    @if (is_array($v))
        @foreach($v as $err)
        <li>{{$err}}</li>
        @endforeach
    @else
        <li>{{(string) $v}}</li>
    @endif
@endforeach
</ul>
