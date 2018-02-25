@extends("layout2")

@section("contents")
<section>
<div class='container half'>
    <h2 class='name'>office name</h2>
    <div class="icons">
        <a href="#"><img src="{{asset('images/new-doc.png')}}"></a>
        <a href="#"><img src="{{asset('images/search.png')}}"></a>
        <a href="#"><img class='red' src="{{asset('images/notify.png')}}"></a>
    </div>
    <div class='body'>
    @include("sub/agent")
    </div>
</div>
<style>
.red {
    background-color: #900;
    border-radius: 20px;
}
.name {
    grid-area: name;
    padding: 10px;
    background-color: #777;
}
.body {
    grid-area: body;
    padding: 10px;
    background-color: #777;
}
.icons {
    background-color: #777;
    grid-area: icons;
    align-self: center;
    padding: 10px;
}
.icons img {
    width: 30px;
    height: 30px;
}
.container {
    display: grid;
    align-content: stretch;
    grid-template-columns: auto;
    grid-template-rows: 1fr 8fr;
    grid-template-areas:
        "name name name icons"
        "body   body   body body";
}
</style>
</section>

<section>
</section>
