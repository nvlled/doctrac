
@extends("layout")

@section("contents")
<section id="document">
    <p class='error'>{{$error ?? ""}}</p>
    <div id="view-document">
        <input id="trackingId" value="{{$trackingId ?? ""}}" type="hidden">
        <input id="document" value="{{$doc ?? ""}}" type="hidden">
        <input id="user" value="{{$user ?? ""}}" type="hidden">
        <h2>
            <span class='title'>doc title</span>
            <small>@ <span class='office'>office</span></small>
        </h2>
        <p class=''>trackingId: <span class='trackingId'></span></p>
        <p><a href="{{route('view-routes', $trackingId)}}">view routes</a></p>
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
        <div class="center">
            <div class="send-data">
                <textarea name="annotation" rows="5" class="full annots"
                placeholder="comments, notes or annotation" ></textarea>
                <br>
                Destination: <select class="offices"></select>
            </div>
            <button class='action half'>SEND / RECEIVE / ABORT SEND / </button>
        </div>
    </div>
    <script src="{{asset('js/sub/document-view.js')}}"></script>
</section>
@endsection



