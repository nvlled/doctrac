
@extends("layout")

<!--
TODO
@section("prefetch")
    @@include("sub/prefetch", "/api/users/self")
    @@include("sub/prefetch", "/api/users/self")
@endsection
-->

@section("contents")
<section id="document">
    <p class='error'>{{$error ?? ""}}</p>
    <div id="view-document">
        <input id="trackingId" value="{{$trackingId ?? ""}}" type="hidden">
        <input id="routeId" value="{{$routeId ?? ""}}" type="hidden">
        <input id="document" value="{{$doc ?? ""}}" type="hidden">
        <input id="user" value="{{$user ?? ""}}" type="hidden">
        <h2>
            <span class='title'>doc title</span>
            <small>@ <span class='office'>office</span></small>
        </h2>
        <p>trackingId: <a class="trackingId" href="{{route('view-routes', $trackingId)}}">{{$trackingId}}</a></p>
        <p class=''>status: <span class='status'>status</span></p>
        <pre>details: <span class='details'></span></pre>
        <pre>annotations: <span class='annotations'></span></pre>
        <p class='info attachment'>
            attachment:
            <a href="#" target="_blank">filename.docx</a>
        </p>

        <div class=''>
            <button class='action hidden'>send/receive</button>
        </div>
        <pre>seen by: <span class='seen-by'></span></pre>
        <hr>
        <p>activity log: </p>
        <ul class='activities'>
        </ul>
        <div class="">
            <div class="send-data hidden">
                <!--TODO-->
                <textarea name="annotation" rows="5" class="full annots"
                placeholder="comments, notes or annotation" ></textarea>
                <br>
                <strong>Destination</strong>
                @include("sub/office-selection")
            </div>
            <div class="center">
                <button class='action half'>SEND / RECEIVE / ABORT SEND / </button>
            </div>
        </div>
    </div>
    <script src='{{asset("js/sub/office-selection.js")}}'></script>
    <script src="{{asset('js/sub/document-view.js')}}"></script>
</section>
@endsection
