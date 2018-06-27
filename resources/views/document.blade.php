
@extends("layout")

@section("contents")
<section id="document" class="container">
    <div class="row">
    <div class="col-10">
    <p class='error'>{{$error ?? ""}}</p>
    <div id="view-document">
        <input id="trackingId" value="{{$trackingId ?? ""}}" type="hidden">
        <input id="routeId" value="{{$routeId ?? ""}}" type="hidden">
        <input id="document" value="{{$doc ?? ""}}" type="hidden">
        <input id="user" value="{{$user ?? ""}}" type="hidden">
        <h2>
            <span class='title'>{{$document->title}}</span>
            <small>@ <span class='office'>{{$route->office->complete_name}}</span></small>
        </h2>

        <div class="row">
            <strong class="col-2 text-right">trackingId</strong>
            <a class="trackingId"
               href="{{route('view-routes', $trackingId)}}">
                {{$trackingId}}
            </a>
        </div>
        <div class="row">
            <strong class="col-2 text-right">state</strong>
            <span class="{{$document->state}}">
                {{$document->state}}
            </span>
        </div>
        <div class="row">
            <strong class="col-2 text-right">details</strong>
            <span class="{{$document->details}}">
                {{$document->details}}
            </span>
        </div>

        @php $seenBy = optional($route->seen_by) @endphp
        <div class="row {{hiddenIf(!$seenBy->count())}}">
            <strong class="col-2 text-right">seen by</strong>
            <span class='seen-by'>
            @if ($seenBy->count())
                {{$seenBy->implode(", ") }}
            @endif
            </span>
        </div>

        <div class="row bg-light">
            <strong class="col-2 text-right">activity log </strong>
            <div class="col-12"></div>
            <div class="offset-1 col-10">
                <ul class=''>
                @php
                $activities = $route->activities;
                @endphp
                @foreach ($activities as $act)
                    <li class="">{{$act}}</li>
                @endforeach
                @if (isEmpty($activities))
                    <em>(none)</em>
                @endif
                </ul>
            </div>
        </div>

        <form class="form-style-1">
        <div class="send-data row hidden">
            <div class="text-center">
                @include("sub.loading")
            </div>
            <div class="offset-lg-1 col-lg-8 offset-md-0 col-md-12">
                <textarea name="annotation" rows="5" class="full annots form-control"
                    placeholder="comments, notes or annotation" ></textarea>
            </div>
            <div class="offset-lg-1 col-lg-6 offset-md-0 col-md-11">
                <div class="dom"></div>
            </div>
        </div>
        <div class="row">
            <div class="offset-lg-1 col-lg-11 offset-md-0 col-md-12">
            <button class='d-none w-25 btn btn-primary hidden action half send'>send</button>
            <div class="col-12"></div>
            <button class='d-none w-25 btn btn-primary hidden action half recv'>receive</button>
            <div class="col-12"></div>
            <button class='d-none w-25 btn btn-default hidden action finalize half affirm green'>finalize</button>
            <div class="col-12"></div>
            <button class='d-none w-25 btn btn-default hidden action reject half red'>reject</button>
            <div class="col-12"></div>
            <button class='d-none w-25 btn btn-primary hidden action return half '>return</button>
            <div class="col-12"></div>
            </div>
        </div>
        <ul class="errors"></ul>
        </form>
    </div>
    <script src='{{asset("js/office-graph.js")}}'></script>
    <script src='{{asset("js/view/route-create.js")}}'></script>
    <!--<script src='{{asset("js/sub/office-selection.js")}}'></script>-->
    <script src="{{asset('js/sub/document-view.js')}}"></script>
    <style>
    button.action {
    }
    </style>
    </div>
    </div>
</section>
@endsection
