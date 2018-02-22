@extends("layout")

@section("contents")

@if (Auth::user())
@include("sub/agent")
@else
<section>
    <em>No Logged-In User</em>
</section>
@endif



@endsection
