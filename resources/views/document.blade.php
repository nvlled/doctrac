
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
        <p class="info"><strong>tracking ID:</strong>
            <a class="trackingId"
               href="{{route('view-routes', $trackingId)}}">
                {{$trackingId}}
            </a>
            @if ($document->type == "parallel")
            <a href="{{route('view-subroutes', $trackingId)}}"
               class="action">other routes</a>
            @endif
        </p>
        <p class="info"><strong>state: </strong>
            <span class="doc-state {{$document->state}}">
                {{$document->state}}
            </span>
        </p>
        <p class='info'>
            <strong>classification level:</strong>
            <span class='classification'>{{$document->classification}}</span>
        </p>
        <p class='info'>
            <strong>status:</strong>
            <span class='status'>{{$route->status}}</span>
        </p>
        <p class='info'>
            <strong>details:</strong>
            <span class='details'>{{$document->details}}</span>
        </p>

        <p class='info {{hiddenIf($document->type == "parallel" || !$route->annotations)}}'>
            <strong>annotations:</strong>
            <span class='annotations'>{{$route->annotations}}</span>
        </p>

        <p class='info {{hiddenIf(!$route->attachment)}}'>
            <strong>attachment:</strong>
            <a href="{{$document->attachment_url}}" target="_blank">{{$document->attachment_filename}}</a>
        </p>
        @php $seenBy = optional($document->seen_by) @endphp
        <p class='info {{hiddenIf(!$seenBy->count())}}'>
            <strong>seen by:</strong>
            <span class='seen-by'>
            @if ($seenBy->count())
                {{implode($seenBy, " ") }}
            @endif
            </span>
        </p>

        <hr>
        <p class="info ">
            <strong>activity log: </strong>
            <ul class='activities'>
            @foreach ($route->activities as $act)
                <li>{{$act}}</li>
            @endforeach
            </ul>
        </p>

        @include("sub.loading")

        <div class="send-data hidden">
            <!--TODO-->
            <textarea name="annotation" rows="5" class="full annots"
                placeholder="comments, notes or annotation" ></textarea>
            <br>
            <strong>Destination</strong>
            <div class="dom"></div>
        </div>
        <ul class="errors"></ul>
        <div class="center">
            <button class='hidden pure-button-primary hidden action half send'>send</button>
            <button class='hidden pure-button-primary hidden action half recv'>receive</button>
            <button class='hidden pure-button-default hidden action finalize half affirm green'>finalize</button>
            <button class='hidden pure-button-default hidden action reject half red'>reject</button>
            <button class='hidden pure-button-primary hidden action return half '>return</button>
        </div>
    </div>
    <script src='{{asset("js/office-graph.js")}}'></script>
    <script src='{{asset("js/view/route-create.js")}}'></script>
    <!--<script src='{{asset("js/sub/office-selection.js")}}'></script>-->
    <script src="{{asset('js/sub/document-view.js')}}"></script>
</section>
@endsection
