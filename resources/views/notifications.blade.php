
@extends("layout")

@section("contents")
<section id="notifications">
<div class="notifs">
@php
    $startNo = $notifications->startNo;
    $pageNo = $notifications->pageNo;
    $pageTotal = $notifications->pageTotal;
@endphp
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
<ul>
@foreach(range(1, $pageTotal) as $p)
    <li><a class="{{textIf($p == $pageNo, "bracket")}}"
            href="?page={{$p}}">{{$p}}</a>
    </li>
@endforeach
</ul>
</div>
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
