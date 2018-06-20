
@php
    $layoutName = "anon";
@endphp
@if (Auth::user())
    @php $layoutName = "layout" @endphp
@endif
@extends($layoutName)


@section("contents")
@include("sub/routes")
@endsection
