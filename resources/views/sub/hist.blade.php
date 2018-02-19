

<section id="doc-history">
    tracking ID
    <input name="trackingId"
        id="doc-history-userid"
        class="half trackingId autocomplete"
        placeholder="search for tracking ID or title"
        value="{{$trackingId ?? ''}}"
        data-hidetext=true
        data-key="trackingId"
        data-url="/api/docs/search">

    <h3 class="title">
        <span class='contents'></span>
        (<small class='type'>*</small>)
    </h3>
    <pre class='details'></pre>
    <p>Routes</p>
    <h4 class='origin hidden'>Document origin: <span class='contents'></span></h4>
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
