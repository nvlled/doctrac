
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
<div class="col-12">
<table class="table">
@foreach($notifications->items as $notif)
    @php
        $url = $notif["url"];
        if ($notif["unread"])
            $url = addQueryString($url, "?notifid={$notif['id']}")
    @endphp
    <tr class="notif-msg d-flex">
        <td>
            <span class='num'>{{$startNo+$loop->index}}.</span>
        </td>
        <td style="width: 30px">
            @if ($notif["unread"])
                <i class="fas fa-exclamation-circle"></i>
            @endif
        </td>
        <td class="col-2">
            <a href="{{$url}}">{{$notif["officeName"]}}</a>
        </td>
        <td class="col-4">
            <a href="{{$url}}">{{$notif["title"]}}</a>
        </td>
        <td class="col-3">
            {{$notif["date"]}}
            (<small class='diff'>{{$notif["diff"]}}</small>)
        </td>
    </tr>
@endforeach
</table>

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
