
@extends("layout")

@section("contents")
<section id="search-doc">
<form method="POST" class="pure-form pure-form-aligned">
    {{ csrf_field() }}
    <fieldset>
        <div class="pure-control-group">
            <label for="name">Tracking Number</label>
            <input id="trackingId" size="30" name="trackingId" required type="text" placeholder="e.g. campus-2018-0123" value="{{$trackingId ?? ''}}">
            <span class="pure-form-message-inline"></span>
            <button type="submit" class="pure-button pure-button-primary">Search</button>
        </div>
        <div class="pure-controls">
            <span class="pure-form-message-inline error">{{$message ?? ""}}</span>
        </div>
    </fieldset>
</form>
</section>
@endsection
