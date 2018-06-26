

@php
    $layoutName = "anon";
@endphp
@if (Auth::user())
    @php $layoutName = "layout" @endphp
@endif
@extends($layoutName)

@section("contents")
<section id="search-doc" class="container">
    <form method="POST"
            class="form-style-1 pure-form pure-form-aligned"
            novalidate>
        {{ csrf_field() }}
        <div class="form-group row">
            <div class="offset-2 col-7 text-left text-danger">
                <span class="error"></span>
            </div>
        </div>
        <div class="form-group row">
            <label for="trackingId" class="col-2 col-form-label text-right">Tracking Number</label>
            <div class="col-7">
                <input id="trackingId" class="form-control {{textIf($message??"", 'is-invalid')}}" size="30" name="trackingId" type="text" placeholder="e.g. campus-2018-0123" value="{{$trackingId ?? ''}}">
                <div class="invalid-feedback">
                    {{$message ?? ""}}
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-2 col-7 text-left clearfix">
                <a class="bracket float-left" href="{{route('search-history')}}">advanced search</a>
                <button type="submit" class="w-25 float-right btn btn-primary">Search</button>
            </div>
        </div>
        <div class="form-group">
        </div>
    </form>
</section>
@endsection
