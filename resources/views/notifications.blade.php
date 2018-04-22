
@extends("layout")

@section("contents")
<section id="notifications">
@php
    $numItems = $notifications->numItems;
    $startNo = $notifications->startNo;
    $pageNo = $notifications->pageNo;
    $numPages = $notifications->numPages;
@endphp
@if ($numItems > 0)
<div class="notifs">
@foreach($notifications->items as $notif)
    <div class="notif-msg {{textIf($notif["unread"], "unread")}}">
        <strong class='num'>{{$startNo+$loop->index}}</strong>
        <a href="{{$notif["url"]}}">
            <span class='contents'>{{$notif["message"]}}</span>
        </a>
        (<small class='diff'>{{$notif["diff"]}}</small>)
    </div>
@endforeach
</div>

<div class="notif-nav">
    @if ($numPages > 1)
        <ul>
        @foreach(range(1, $numPages) as $p)
            <li><a class="{{textIf($p == $pageNo, "bracket")}}"
                    href="?page={{$p}}">{{$p}}</a>
            </li>
        @endforeach
        </ul>
    @endif

@else
    <div class="center"><em>(no notifications)</em></div>
</div> <!--div.notifs-->
@endif



<div class="loading-msg hidden">loading...</div>
<div class='center'>
    <button class='hidden'>clear notifications</button>
</div>

</section>
<style>
.notif-msg {
    padding: 10px;
    border-bottom: 1px solid gray;
}
.num {
    padding-right: 10px;
}
.notif-nav li {
    display: inline-block;
}

</style>
<script>

</script>
@endsection
