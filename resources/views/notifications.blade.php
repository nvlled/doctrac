
@extends("layout")

@section("contents")
<section id="notifications">
<em>TODO</em>
@foreach($messages as $msg)
    <div class="notif-msg">
    <input type="hidden" name="route-id" value="{{$msg['routeId']}}">
    <strong class='num'>{{$loop->iteration}}</strong> 
    {{$msg['contents']}}
    (<small>{{$msg['date']->diffForHumans()}}</small>)
    </div>
@endforeach
<br>
<div class='center'>
    <button>clear notifications</button>
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
</style>
<script>
api.util.urlFor({
    routeName: "view-document",
    routeId:  "___",
}).then(function(resp) {
    var url = resp.url
    function createLink(routeId, trackingId) {
        var href = url.replace("___", routeId);
        return "<a href='"+href+"'>"+trackingId+"</a>";
    }
    $(".notif-msg").each(function() {
        var $notif = $(this);
        var html = $notif.html();
        var idPattern = /\w{3,5}-\d{4}-\d+/;
        var routeId = $notif.find("input[name=route-id]").val();
        html = html.replace(idPattern, function(trackingId) {
            return createLink(routeId, trackingId);
        });
        $notif.html(html);
    });
});
</script>
@endsection
