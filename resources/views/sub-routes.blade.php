
@extends("layout")

@section("contents")
<section id="document-subroutes">
    <p class='error'>{{$error ?? ""}}</p>
    <div id="view-document">
        <input id="trackingId" value="{{$trackingId ?? ""}}" type="hidden">
        <input id="routeId" value="{{$routeId ?? ""}}" type="hidden">
        <input id="document" value="{{$doc ?? ""}}" type="hidden">
        <input id="user" value="{{$user ?? ""}}" type="hidden">
        <h2>
            <span class='title'>{{$doc->title}}</span>
            <small>@ <span class='office'>{{$origin->office_name}}</span></small>
        </h2>
        <p class="info"><strong>tracking ID:</strong>
            <a class="trackingId"
               href="{{route('view-routes', $doc->trackingId)}}">
                {{$doc->trackingId}}
            </a>
        </p>
        <h3>Destinations</h3>
        @foreach ($routes as $route)
            <ul>
                <li>
                    <a href="{{$route->link}}">{{$route->nextRoute->office_name}}</a>
                </li>
            </ul>
        @endforeach
    </div>
</section>
@endsection
