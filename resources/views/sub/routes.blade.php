
<section id="doc-history">
    <input id="document" value="{{$doc ?? ""}}" type="hidden">
    <input id="user" value="{{$user ?? ""}}" type="hidden">

    <h2 class="title">
        <span class='contents'></span>
        (<small class='type'>*</small>)
    </h2>
    <p class="info"><strong>tracking ID:</strong>
        <span class="trackingId">
            {{$doc->trackingId}}
        </span>
    </p>
    <p class='info'>
        <strong>details:</strong>
        <span class='details'></span>
    </p>
    <p class='info attachment'><strong>attachment</strong>: <a href="#" target="_blank">filename.docx</a></p>

    <table class='full'>
        <thead>
        <tr>
            <th>id</th>
            <th>campus</th>
            <th>office</th>
            <th>status</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script src="{{asset('js/sub/routes.js')}}"></script>
</section>
