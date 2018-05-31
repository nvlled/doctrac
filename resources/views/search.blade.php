
@extends("layout")

@section("contents")
<section id="search-doc">
<form method="POST" class="form-style-1 pure-form pure-form-aligned">
    {{ csrf_field() }}
    <h2 class="center">Search</h2>
    <div class="pure-control-group">
        <label for="name">Tracking Number</label>
        <input id="trackingId" size="30" name="trackingId" required type="text" placeholder="e.g. campus-2018-0123" value="{{$trackingId ?? ''}}">
        <span class="pure-form-message-inline"></span>
    </div>
    <div class="right">
        <a class="bracket" href="{{route('search-history')}}">advanced search</a>
        <button type="submit" class="pure-button pure-button-primary">Search</button>
    </div>
    <div class="center">
        <span class="pure-form-message-inline error">{{$message ?? ""}}</span>
    </div>
</form>
</section>
@endsection
