
@extends("layout")

@section("contents")
<section id="notifications container">
@php
    $numItems = $notifications->numItems;
    $startNo = $notifications->startNo;
    $pageNo = $notifications->pageNo;
    $numPages = $notifications->numPages;
@endphp
@if ($numItems > 0)
<div class="notifs row">
<div class="col-10">
@foreach($notifications->items as $notif)
    <ul class="list-group notif-msg ">
        <li class="list-group-item {{textIf($notif["unread"], "bg-light")}}">
            <span class='num'>{{$startNo+$loop->index}}.</span>
            @php
                $url = $notif["url"];
                if ($notif["unread"])
                    $url = addQueryString($url, "?notifid={$notif['id']}")
            @endphp
            <a href="{{$url}}">
                <span class='contents '>{{$notif["message"]}}</span>
            </a>
            (<small class='diff'>{{$notif["diff"]}}</small>)
            @if ($notif["unread"])
                <i class="fas fa-exclamation-circle"></i>
            @endif
        </li>
    </ul>
@endforeach
</div>
</div>

<div class="notif-nav row">
    <div class="col">
    @if ($numPages > 1)
        <ul class="pagination">
        @foreach(range(1, $numPages) as $p)
            <li class="page-item {{textIf($p == $pageNo, "active")}}"><a class="page-link"
                    href="?page={{$p}}">{{$p}}</a>
            </li>
        @endforeach
        </ul>
    @endif
    </div>
@else
    <div class="center"><em>(no notifications)</em></div>
</div> <!--div.notifs-->
@endif

</section>
@endsection
