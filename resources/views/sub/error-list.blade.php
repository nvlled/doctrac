


@php
$errors = $errors ?? [];
@endphp
<div class="">
<ul class="errors text-danger">
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
</div>
